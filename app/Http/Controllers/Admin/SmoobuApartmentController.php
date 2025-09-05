<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SmoobuClient;
use App\Models\SmoobuApartmentMeta;
use App\Models\SmoobuApartmentImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SmoobuApartmentController extends Controller
{
    public function edit($apartmentId, SmoobuClient $smoobu)
    {
        $apartmentId = (int) $apartmentId;

        // Trae info del API (opcional, solo para mostrar datos “reales” de Smoobu)
        $detail = Cache::remember("smoobu.apartment.$apartmentId", 300, fn () => $smoobu->apartment($apartmentId));

        // Meta local (o crear una nueva instancia por si no existe)
        $meta = SmoobuApartmentMeta::firstOrNew(['apartment_id' => $apartmentId]);

        // Imágenes locales activas
        $images = SmoobuApartmentImage::where('apartment_id', $apartmentId)
                    ->orderBy('sort_order')->get();

        return view('admin.smoobu_apartments.edit', [
            'apartmentId' => $apartmentId,
            'detail'      => $detail,
            'meta'        => $meta,
            'images'      => $images,
        ]);
    }

    public function update($apartmentId, Request $request)
    {
        $apartmentId = (int) $apartmentId;

        $data = $request->validate([
            'title'                  => 'nullable|string|max:255',
            'slug'                   => 'nullable|string|max:255',
            'description'            => 'nullable|string',
            'city'                   => 'nullable|string|max:255',
            'country'                => 'nullable|string|max:255',
            'bedrooms'               => 'nullable|integer|min:0',
            'bathrooms'              => 'nullable|numeric|min:0',
            'base_price_override'    => 'nullable|numeric|min:0',
            'calendar_verification'  => 'nullable|string|max:255',
            'is_published'           => 'nullable|boolean',
            'sort_order'             => 'nullable|array',
            'sort_order.*'           => 'integer',
            'cover_image_id'         => 'nullable|integer|exists:smoobu_apartment_images,id',
            'delete_images'          => 'nullable|array',
            'delete_images.*'        => 'integer|exists:smoobu_apartment_images,id',
            'images.*'               => 'nullable|image|max:4096',
        ]);

        // Upsert meta
        $meta = SmoobuApartmentMeta::firstOrNew(['apartment_id' => $apartmentId]);
        $meta->fill([
            'title'                 => $data['title'] ?? $meta->title,
            'slug'                  => $data['slug']  ?? $meta->slug,
            'description'           => $data['description'] ?? $meta->description,
            'city'                  => $data['city'] ?? $meta->city,
            'country'               => $data['country'] ?? $meta->country,
            'bedrooms'              => $data['bedrooms'] ?? $meta->bedrooms,
            'bathrooms'             => $data['bathrooms'] ?? $meta->bathrooms,
            'base_price_override'   => $data['base_price_override'] ?? $meta->base_price_override,
            'calendar_verification' => $data['calendar_verification'] ?? $meta->calendar_verification,
            'is_published'          => $request->boolean('is_published'),
        ]);

        // Generar slug si no viene
        if (empty($meta->slug) && !empty($meta->title)) {
            $meta->slug = Str::slug($meta->title) . '-' . $apartmentId;
        }

        $meta->apartment_id = $apartmentId;
        $meta->save();

        // Eliminar imágenes seleccionadas
        if (!empty($data['delete_images'])) {
            $toDelete = SmoobuApartmentImage::whereIn('id', $data['delete_images'])
                        ->where('apartment_id', $apartmentId)->get();

            foreach ($toDelete as $img) {
                // borra archivo físico si existe
                $abs = public_path($img->path);
                if (is_file($abs)) @unlink($abs);
                $img->delete();
            }
        }

        // Reordenar imágenes (sort_order[image_id] = int)
        if (!empty($data['sort_order'])) {
            foreach ($data['sort_order'] as $imgId => $order) {
                SmoobuApartmentImage::where('id', $imgId)
                    ->where('apartment_id', $apartmentId)
                    ->update(['sort_order' => (int) $order]);
            }
        }

        // Subir nuevas imágenes
        if ($request->hasFile('images')) {
            $dir = public_path("images/smoobu/$apartmentId");
            if (!is_dir($dir)) @mkdir($dir, 0775, true);

            // último sort_order actual
            $maxOrder = (int) SmoobuApartmentImage::where('apartment_id', $apartmentId)->max('sort_order');

            foreach ($request->file('images') as $file) {
                if (!$file->isValid()) continue;
                $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $file->move($dir, $filename);

                SmoobuApartmentImage::create([
                    'apartment_id' => $apartmentId,
                    'path'         => "images/smoobu/$apartmentId/$filename",
                    'is_active'    => true,
                    'sort_order'   => ++$maxOrder,
                ]);
            }
        }

        // Cambiar portada si se eligió una imagen
        if (!empty($data['cover_image_id'])) {
            $cover = SmoobuApartmentImage::where('id', $data['cover_image_id'])
                ->where('apartment_id', $apartmentId)->first();
            if ($cover) {
                $meta->cover_image_path = $cover->path;
                $meta->save();
            }
        }

        return back()->with('success', 'Propiedad actualizada correctamente.');
    }

    public function destroyImage(SmoobuApartmentImage $image)
    {
        // (opcional) validar permisos aquí
        $abs = public_path($image->path);
        if (is_file($abs)) @unlink($abs);
        $image->delete();

        return back()->with('success', 'Imagen eliminada.');
    }
}
