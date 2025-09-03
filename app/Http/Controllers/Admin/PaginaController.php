<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pagina;
use App\Models\PaginaMeta;


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
            'card1_image_1', 'card1_image_2', 'card1_image_3',
            'card2_image_4', 'card2_image_5', 'card2_image_6', 'card2_image_7',
        ]));
    
        // Subir logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_logo.' . $file->getClientOriginalExtension();
            $file->move('/home/u274930358/domains/hostella.co/public_html/images/', $filename);
            $pagina->logo = $filename;
        }
    
        // Subir imágenes de tarjetas sección 1
        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile("card1_image_$i")) {
                $file = $request->file("card1_image_$i");
                $filename = time() . "_card1_$i." . $file->getClientOriginalExtension();
                $file->move('/home/u274930358/domains/hostella.co/public_html/images/', $filename);
                $pagina->{"card1_image_$i"} = $filename;
            }
        }
    
        // Subir imágenes de tarjetas sección 2 (confianza en Hostella)
        for ($i = 4; $i <= 7; $i++) {
            if ($request->hasFile("card2_image_$i")) {
                $file = $request->file("card2_image_$i");
                $filename = time() . "_card2_$i." . $file->getClientOriginalExtension();
                $file->move('/home/u274930358/domains/hostella.co/public_html/images/', $filename);
                $pagina->{"card2_image_$i"} = $filename;
            }
        }
    
        $pagina->save();
    
        // Guardar metadatos SEO
        if ($request->has('meta_title')) {
            $meta = $pagina->meta ?? new \App\Models\PaginaMeta();
            $meta->fill($request->only([
                'meta_title', 'meta_description', 'meta_keywords',
                'canonical_url', 'robots', 'author', 'language', 'viewport', 'charset'
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
            $file->move('/home/u274930358/domains/hostella.co/public_html/images/', $filename); // Guarda en /public/images
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
    

}
