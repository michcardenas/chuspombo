<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pagina;
use App\Models\PaginaMeta;
use App\Models\ContactPage;

class PaginaController extends Controller
{
    public function update(Request $request, $id)
    {
        // Siempre trabajamos sobre ID 1
        $pagina = \App\Models\Pagina::find($id);

        if (!$pagina) {
            $pagina = new \App\Models\Pagina();
            $pagina->id = 1; // Forzamos que siempre sea ID 1
        }

        // Llenamos todos los campos excepto logo e imágenes
        $pagina->fill($request->except([
            'logo',
            'card1_image_1',
            'card1_image_2',
            'card1_image_3',
            'card2_image_4',
            'card2_image_5',
            'card2_image_6',
            'card2_image_7',
        ]));

        // Subir logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_logo.' . $file->getClientOriginalExtension();
            $file->move('/home/u284093604/domains/chuspomboapartamentos.com/public_html/images/', $filename);
            $pagina->logo = $filename;
        }

        // Subir imágenes de tarjetas sección 1
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile("card1_image_$i")) {
                $file = $request->file("card1_image_$i");
                $filename = time() . "_card1_$i." . $file->getClientOriginalExtension();
                $file->move('/home/u284093604/domains/chuspomboapartamentos.com/public_html/images/', $filename);
                $pagina->{"card1_image_$i"} = $filename;
            }
        }

        // Subir imágenes de tarjetas sección 2 (confianza en Hostella)
        for ($i = 4; $i <= 7; $i++) {
            if ($request->hasFile("card2_image_$i")) {
                $file = $request->file("card2_image_$i");
                $filename = time() . "_card2_$i." . $file->getClientOriginalExtension();
                $file->move('/home/u284093604/domains/chuspomboapartamentos.com/public_html/images/', $filename);
                $pagina->{"card2_image_$i"} = $filename;
            }
        }

        $pagina->save();

        // Guardar metadatos SEO
        if ($request->has('meta_title')) {
            $meta = $pagina->meta ?? new \App\Models\PaginaMeta();
            $meta->fill($request->only([
                'meta_title',
                'meta_description',
                'meta_keywords',
                'canonical_url',
                'robots',
                'author',
                'language',
                'viewport',
                'charset'
            ]));
            $meta->pagina_id = $pagina->id;
            $meta->save();
        }

        return redirect()->route('admin.dashboard')->with('success', 'Página actualizada con éxito.');
    }


    public function editPropiedades()
    {
        $paginaPropiedades = \App\Models\Pagina::with('meta')->where('id', 2)->first();

        if (!$paginaPropiedades) {
            $paginaPropiedades = \App\Models\Pagina::create([
                'id' => 2,
                'h1' => '',
                'h2_1' => ''
            ]);
        }

        if (!$paginaPropiedades->meta) {
            $paginaPropiedades->setRelation('meta', new \App\Models\PaginaMeta([
                'pagina_id' => $paginaPropiedades->id
            ]));
        }

        return view('admin.edit-propiedades', compact('paginaPropiedades'));
    }


    public function updatePropiedades(Request $request)
    {
        $data = $request->validate([
            'h1' => 'nullable|string|max:255',
            'h2_1' => 'nullable|string|max:255',
            'card2_image_4' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:255',
            'robots' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'viewport' => 'nullable|string|max:255',
            'charset' => 'nullable|string|max:255',
        ]);

        $paginaData = [
            'h1' => $data['h1'],
            'h2_1' => $data['h2_1'],
        ];

        // Procesar imagen si fue subida
        if ($request->hasFile('card2_image_4')) {
            $file = $request->file('card2_image_4');
            $filename = time() . '_card2_4.' . $file->getClientOriginalExtension();
            $file->move('/home/u284093604/domains/chuspomboapartamentos.com/public_html/images/', $filename); // Guarda en /public/images
            $paginaData['card2_image_4'] = $filename; // Solo el nombre se guarda en la base de datos
        }


        // Crear o actualizar la página
        $pagina = Pagina::updateOrCreate(['id' => 2], $paginaData);

        // Crear o actualizar la meta
        $pagina->meta()->updateOrCreate([], [
            'meta_title' => $data['meta_title'] ?? '',
            'meta_description' => $data['meta_description'] ?? '',
            'meta_keywords' => $data['meta_keywords'] ?? '',
            'canonical_url' => $data['canonical_url'] ?? '',
            'robots' => $data['robots'] ?? '',
            'author' => $data['author'] ?? '',
            'language' => $data['language'] ?? '',
            'viewport' => $data['viewport'] ?? '',
            'charset' => $data['charset'] ?? '',
        ]);

        return redirect()->route('admin.pagina.propiedades.edit')->with('success', 'Contenido actualizado correctamente.');
    }

    public function editContacto()
    {
        // Editamos un único registro (el primero activo o el primero)
        $contact = ContactPage::orderByDesc('is_active')->orderBy('id')->first();

        if (!$contact) {
            $contact = ContactPage::create([
                'h1' => '',
                'h2' => '',
                'intro_text' => '',
                'side_text'  => '',
                'is_active'  => 1,
            ]);
        }

        // Normalizar business_hours a array para el form
        $hours = [];
        if ($contact->business_hours) {
            $decoded = json_decode($contact->business_hours, true);
            if (is_array($decoded)) $hours = $decoded;
        }
        $contact->business_hours_array = $hours;

        return view('admin.edit-contacto', compact('contact'));
    }

    public function updateContacto(Request $request)
    {
        // Validación basada en tu esquema
        $validated = $request->validate([
            'h1'                 => 'nullable|string|max:255',
            'h2'                 => 'nullable|string|max:255',
            'intro_text'         => 'nullable|string',
            'side_text'          => 'nullable|string',

            'email_primary'      => 'nullable|email|max:255',
            'email_secondary'    => 'nullable|email|max:255',
            'phone_primary'      => 'nullable|string|max:255',
            'phone_secondary'    => 'nullable|string|max:255',
            'whatsapp'           => 'nullable|string|max:255',
            'website'            => 'nullable|url|max:255',

            'address_line1'      => 'nullable|string|max:255',
            'address_line2'      => 'nullable|string|max:255',
            'city'               => 'nullable|string|max:255',
            'region'             => 'nullable|string|max:255',
            'postal_code'        => 'nullable|string|max:255',
            'country'            => 'nullable|string|max:255',

            'map_embed_url'      => 'nullable|string|max:255',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',

            'facebook_url'       => 'nullable|url|max:255',
            'instagram_url'      => 'nullable|url|max:255',
            'twitter_url'        => 'nullable|url|max:255',
            'tiktok_url'         => 'nullable|url|max:255',
            'youtube_url'        => 'nullable|url|max:255',
            'linkedin_url'       => 'nullable|url|max:255',

            'form_recipient'     => 'nullable|email|max:255',
            'form_cc'            => 'nullable|string|max:255',
            'success_message'    => 'nullable|string|max:255',
            'legal_checkbox_label' => 'nullable|string|max:255',
            'legal_link_url'     => 'nullable|url|max:255',

            // business_hours llega como arreglo de filas [{label,from,to}]
            'business_hours'     => 'nullable|array',
            'business_hours.*.label' => 'nullable|string|max:255',
            'business_hours.*.from'  => 'nullable|string|max:255',
            'business_hours.*.to'    => 'nullable|string|max:255',

            // Archivos
            'hero_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
            'banner_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',

            'is_active'          => 'nullable|boolean',
        ]);

        // Obtenemos/creamos el único registro
        $contact = ContactPage::orderByDesc('is_active')->orderBy('id')->first();
        if (!$contact) {
            $contact = new ContactPage();
        }

        // Campos directos (todos excepto archivos y business_hours)
        $fillableKeys = [
            'h1',
            'h2',
            'intro_text',
            'side_text',
            'email_primary',
            'email_secondary',
            'phone_primary',
            'phone_secondary',
            'whatsapp',
            'website',
            'address_line1',
            'address_line2',
            'city',
            'region',
            'postal_code',
            'country',
            'map_embed_url',
            'latitude',
            'longitude',
            'facebook_url',
            'instagram_url',
            'twitter_url',
            'tiktok_url',
            'youtube_url',
            'linkedin_url',
            'form_recipient',
            'form_cc',
            'success_message',
            'legal_checkbox_label',
            'legal_link_url',
        ];
        foreach ($fillableKeys as $key) {
            $contact->{$key} = $validated[$key] ?? null;
        }

        // business_hours → JSON
        $hours = $validated['business_hours'] ?? [];
        // Limpieza mínima (quitar filas vacías)
        $normalized = [];
        foreach ($hours as $row) {
            $label = trim($row['label'] ?? '');
            $from  = trim($row['from']  ?? '');
            $to    = trim($row['to']    ?? '');
            if ($label !== '' || $from !== '' || $to !== '') {
                $normalized[] = compact('label', 'from', 'to');
            }
        }
        $contact->business_hours = $normalized ? json_encode($normalized, JSON_UNESCAPED_UNICODE) : null;

        // Cargar archivos (ruta absoluta que estás usando en otros lugares)
        $diskPath = '/home/u284093604/domains/chuspomboapartamentos.com/public_html/images/';

        if ($request->hasFile('hero_image')) {
            $file = $request->file('hero_image');
            $filename = time() . '_hero.' . $file->getClientOriginalExtension();
            $file->move($diskPath, $filename);
            $contact->hero_image = $filename;
        }

        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $filename = time() . '_banner.' . $file->getClientOriginalExtension();
            $file->move($diskPath, $filename);
            $contact->banner_image = $filename;
        }

        // Estado
        $contact->is_active = $request->boolean('is_active');

        $contact->save();

        return back()->with('success', 'Página de contacto actualizada correctamente.');
    }
}
