<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pagina;
use App\Models\PaginaMeta;
use App\Models\ContactPage;
use App\Models\AboutPage; // <-- agrega este use arriba

class PaginaController extends Controller
{
   public function update(Request $request, $id)
{
    // Validaciones mínimas
    $request->validate([
        'featured_property_id' => 'nullable|string|max:100',

        // 👇 Nuevos
        'direccion' => ['nullable', 'string', 'max:255'],
        'email'     => ['nullable', 'email:rfc', 'max:255'],

        // Galería del carrusel
        'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        'gallery_titles.*' => 'nullable|string|max:255',
        'existing_images.*' => 'nullable|string|max:255',

        // Si quieres validar archivos, descomenta:
        // 'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card1_image_1'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card1_image_2'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card1_image_3'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card2_image_4'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card2_image_5'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card2_image_6'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        // 'card2_image_7'   => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
    ]);

    // Siempre trabajamos sobre ID 1
    $pagina = \App\Models\Pagina::find($id);
    if (!$pagina) {
        $pagina = new \App\Models\Pagina();
        $pagina->id = 1; // Forzamos que siempre sea ID 1
    }

    // Llenamos todos los campos excepto archivos e imágenes
    $pagina->fill($request->except([
        'logo',
        'card1_image_1',
        'card1_image_2',
        'card1_image_3',
        'card2_image_4',
        'card2_image_5',
        'card2_image_6',
        'card2_image_7',
        'gallery_images',
        'gallery_titles',
        'existing_images',
    ]));

    // Ruta de uploads
    $diskPath = '/home/u284093604/domains/chuspomboapartamentos.com/public_html/images/';

    // Subir logo
    if ($request->hasFile('logo')) {
        $file = $request->file('logo');
        $filename = time() . '_logo.' . $file->getClientOriginalExtension();
        $file->move($diskPath, $filename);
        $pagina->logo = $filename;
    }

    // Subir imágenes de tarjetas sección 1
    for ($i = 1; $i <= 3; $i++) {
        if ($request->hasFile("card1_image_$i")) {
            $file = $request->file("card1_image_$i");
            $filename = time() . "_card1_$i." . $file->getClientOriginalExtension();
            $file->move($diskPath, $filename);
            $pagina->{"card1_image_$i"} = $filename;
        }
    }

    // Subir imágenes de tarjetas sección 2 (confianza)
    for ($i = 4; $i <= 7; $i++) {
        if ($request->hasFile("card2_image_$i")) {
            $file = $request->file("card2_image_$i");
            $filename = time() . "_card2_$i." . $file->getClientOriginalExtension();
            $file->move($diskPath, $filename);
            $pagina->{"card2_image_$i"} = $filename;
        }
    }

    // ===== PROCESAR GALERÍA DEL CARRUSEL =====
    $galleryData = [];

    // Mantener imágenes existentes que no fueron eliminadas
    $existingImages = $request->input('existing_images', []);
    $galleryTitles = $request->input('gallery_titles', []);

    // Procesar imágenes existentes
    foreach ($existingImages as $index => $imageName) {
        if (!empty($imageName)) {
            $galleryData[] = [
                'image' => $imageName,
                'title' => $galleryTitles[$index] ?? ''
            ];
        }
    }

    // Procesar nuevas imágenes subidas
    if ($request->hasFile('gallery_images')) {
        $newImages = $request->file('gallery_images');
        $newTitles = array_slice($galleryTitles, count($existingImages));

        foreach ($newImages as $index => $file) {
            if ($file && $file->isValid()) {
                $timestamp = time() + $index; // Evitar nombres duplicados
                $filename = $timestamp . '_gallery.' . $file->getClientOriginalExtension();
                $file->move($diskPath, $filename);

                $galleryData[] = [
                    'image' => $filename,
                    'title' => $newTitles[$index] ?? ''
                ];
            }
        }
    }

    // Guardar en JSON
    $pagina->gallery_images = json_encode($galleryData, JSON_UNESCAPED_UNICODE);

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

            // FAQs por defecto vacíos
            'faq1_q' => null, 'faq1_a' => null,
            'faq2_q' => null, 'faq2_a' => null,
            'faq3_q' => null, 'faq3_a' => null,
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
    // Validación basada en tu esquema (+ FAQs)
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

        'map_embed_url'      => 'nullable|string',
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
        'business_hours'         => 'nullable|array',
        'business_hours.*.label' => 'nullable|string|max:255',
        'business_hours.*.from'  => 'nullable|string|max:255',
        'business_hours.*.to'    => 'nullable|string|max:255',

        // Archivos
        'hero_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',
        'banner_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:4096',

        'is_active'          => 'nullable|boolean',
        'is_24_hours'        => 'nullable|boolean',

        // ===== FAQs =====
        'faq1_q' => 'nullable|string|max:255',
        'faq1_a' => 'nullable|string',
        'faq2_q' => 'nullable|string|max:255',
        'faq2_a' => 'nullable|string',
        'faq3_q' => 'nullable|string|max:255',
        'faq3_a' => 'nullable|string',
    ]);

    // Obtenemos/creamos el único registro
    $contact = ContactPage::orderByDesc('is_active')->orderBy('id')->first();
    if (!$contact) {
        $contact = new ContactPage();
    }

    // Campos directos (todos excepto archivos y business_hours)
    $fillableKeys = [
        'h1','h2','intro_text','side_text',
        'email_primary','email_secondary','phone_primary','phone_secondary','whatsapp','website',
        'address_line1','address_line2','city','region','postal_code','country',
        'map_embed_url','latitude','longitude',
        'facebook_url','instagram_url','twitter_url','tiktok_url','youtube_url','linkedin_url',
        'form_recipient','form_cc','success_message','legal_checkbox_label','legal_link_url',

        // ===== FAQs =====
        'faq1_q','faq1_a','faq2_q','faq2_a','faq3_q','faq3_a',
    ];
    foreach ($fillableKeys as $key) {
        $contact->{$key} = $validated[$key] ?? null;
    }

    // business_hours → JSON (limpieza mínima)
    $hours = $validated['business_hours'] ?? [];
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

    // Cargar archivos
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
    $contact->is_24_hours = $request->boolean('is_24_hours');

    $contact->save();

    return back()->with('success', 'Página de contacto actualizada correctamente.');
}


  public function editNosotros()
    {
        $about = AboutPage::first() ?? new AboutPage();

        // Prefill para los nombres del FORM (h1, h2_1, etc.)
        $about->setAttribute('h1',                       $about->hero_title            ?? 'Chuspombo');
        $about->setAttribute('h2_1',                     $about->hero_subtitle         ?? 'Apartamentos');
        $about->setAttribute('cta_primary_text',         $about->hero_cta_text         ?? 'Descubre Tu Refugio Gallego');
        $about->setAttribute('cta_primary_url',          $about->hero_cta_url          ?? url('/contacto'));

        $about->setAttribute('h2_historia',              $about->story_title           ?? 'Más que alojamiento, una experiencia auténtica');
        $about->setAttribute('p_historia',               $about->story_text            ?? '');

        $about->setAttribute('h2_experiencia',           $about->exp_title             ?? 'Vive Galicia como nunca antes');
        $about->setAttribute('p_experiencia',            $about->exp_subtitle          ?? '');
        $about->setAttribute('card1_title_1',            $about->exp_card1_title       ?? 'Gastronomía Auténtica');
        $about->setAttribute('card1_content_1',          $about->exp_card1_text        ?? '');
        $about->setAttribute('card1_title_2',            $about->exp_card2_title       ?? 'Ubicación Privilegiada');
        $about->setAttribute('card1_content_2',          $about->exp_card2_text        ?? '');
        $about->setAttribute('card1_title_3',            $about->exp_card3_title       ?? 'Atención Personalizada');
        $about->setAttribute('card1_content_3',          $about->exp_card3_text        ?? '');

        $about->setAttribute('h2_why',                   $about->why_title             ?? 'La diferencia está en los detalles');
        $about->setAttribute('card2_title_4',            $about->why_item1_title       ?? 'Exclusividad Garantizada');
        $about->setAttribute('card2_content_4',          $about->why_item1_text        ?? '');
        $about->setAttribute('card2_title_5',            $about->why_item2_title       ?? 'Pasión Local');
        $about->setAttribute('card2_content_5',          $about->why_item2_text        ?? '');
        $about->setAttribute('card2_title_6',            $about->why_item3_title       ?? 'Lujo Auténtico');
        $about->setAttribute('card2_content_6',          $about->why_item3_text        ?? '');

        $about->setAttribute('h2_cta',                   $about->cta_title             ?? 'Tu hogar gallego te espera');
        $about->setAttribute('p_cta',                    $about->cta_text              ?? '');
        $about->setAttribute('cta_primary_text_final',   $about->cta_button_text       ?? 'Reserva ahora');
        $about->setAttribute('cta_primary_url_final',    $about->cta_button_url        ?? 'mailto:info@chuspombo.com');
        $about->setAttribute('cta_secondary_text_final', $about->cta_phone_label       ?? 'Llámanos');
        $about->setAttribute('cta_secondary_url_final',  $about->cta_phone_number      ?? 'tel:+34123456789');

        // La vista usa $paginaNosotros (mantenemos ese nombre)
        $paginaNosotros = $about;

        return view('admin.edit-nosotros', compact('paginaNosotros'));
    }

    public function updateNosotros(Request $request)
    {
        // Validación con los NOMBRES DEL FORM
        $data = $request->validate([
            // HERO
            'h1'                      => 'nullable|string|max:255',
            'h2_1'                    => 'nullable|string|max:255',
            'cta_primary_text'        => 'nullable|string|max:255',
            'cta_primary_url'         => 'nullable|string|max:255',

            // HISTORIA
            'h2_historia'             => 'nullable|string|max:255',
            'p_historia'              => 'nullable|string',

            // EXPERIENCIA
            'h2_experiencia'          => 'nullable|string|max:255',
            'p_experiencia'           => 'nullable|string',
            'card1_title_1'           => 'nullable|string|max:255',
            'card1_content_1'         => 'nullable|string',
            'card1_title_2'           => 'nullable|string|max:255',
            'card1_content_2'         => 'nullable|string',
            'card1_title_3'           => 'nullable|string|max:255',
            'card1_content_3'         => 'nullable|string',

            // WHY
            'h2_why'                  => 'nullable|string|max:255',
            'card2_title_4'           => 'nullable|string|max:255',
            'card2_content_4'         => 'nullable|string',
            'card2_title_5'           => 'nullable|string|max:255',
            'card2_content_5'         => 'nullable|string',
            'card2_title_6'           => 'nullable|string|max:255',
            'card2_content_6'         => 'nullable|string',

            // CTA FINAL
            'h2_cta'                  => 'nullable|string|max:255',
            'p_cta'                   => 'nullable|string',
            'cta_primary_text_final'  => 'nullable|string|max:255',
            'cta_primary_url_final'   => 'nullable|string|max:255',
            'cta_secondary_text_final'=> 'nullable|string|max:255',
            'cta_secondary_url_final' => 'nullable|string|max:255',
        ]);

        // MAPEO a los campos REALES del modelo
        $mapped = [
            // HERO
            'hero_title'          => $data['h1']                      ?? null,
            'hero_subtitle'       => $data['h2_1']                    ?? null,
            'hero_cta_text'       => $data['cta_primary_text']        ?? null,
            'hero_cta_url'        => $data['cta_primary_url']         ?? null,

            // HISTORIA
            'story_title'         => $data['h2_historia']             ?? null,
            'story_text'          => $data['p_historia']              ?? null,

            // EXPERIENCIA
            'exp_title'           => $data['h2_experiencia']          ?? null,
            'exp_subtitle'        => $data['p_experiencia']           ?? null,
            'exp_card1_title'     => $data['card1_title_1']           ?? null,
            'exp_card1_text'      => $data['card1_content_1']         ?? null,
            'exp_card2_title'     => $data['card1_title_2']           ?? null,
            'exp_card2_text'      => $data['card1_content_2']         ?? null,
            'exp_card3_title'     => $data['card1_title_3']           ?? null,
            'exp_card3_text'      => $data['card1_content_3']         ?? null,

            // WHY
            'why_title'           => $data['h2_why']                  ?? null,
            'why_item1_title'     => $data['card2_title_4']           ?? null,
            'why_item1_text'      => $data['card2_content_4']         ?? null,
            'why_item2_title'     => $data['card2_title_5']           ?? null,
            'why_item2_text'      => $data['card2_content_5']         ?? null,
            'why_item3_title'     => $data['card2_title_6']           ?? null,
            'why_item3_text'      => $data['card2_content_6']         ?? null,

            // CTA FINAL
            'cta_title'           => $data['h2_cta']                  ?? null,
            'cta_text'            => $data['p_cta']                   ?? null,
            'cta_button_text'     => $data['cta_primary_text_final']  ?? null,
            'cta_button_url'      => $data['cta_primary_url_final']   ?? null,
            'cta_phone_label'     => $data['cta_secondary_text_final']?? null,
            'cta_phone_number'    => $data['cta_secondary_url_final'] ?? null,
        ];

        $about = AboutPage::first() ?? new AboutPage();
        $about->fill($mapped);
        $about->save();

        return back()->with('success', 'Contenido de “Nosotros” actualizado correctamente.');
    }

}
