<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactPage;
use App\Models\AboutPage; // <-- USAR EL MODELO DEDICADO

class AboutController extends Controller
{
    protected $smoobu;

    public function __construct(\App\Services\SmoobuClient $smoobu)
    {
        $this->smoobu = $smoobu;
    }

    /**
     * Muestra la página de "Nosotros"
     */
    public function index(Request $request)
    {
        try {
            // 1) Traer apartamentos desde Smoobu (cache 5 min)
            $apartments = Cache::remember('smoobu.apartments', 300, function () {
                return $this->smoobu->apartments();
            });

            $apartments = collect($apartments ?? []);

            // 2) Imágenes por apartment desde DB (opcional)
            $ids = $apartments->pluck('id')->filter()->unique()->values()->all();

            $imagesByApt = collect();
            if (!empty($ids)) {
                $rows = DB::table('smoobu_apartment_images')
                    ->select('apartment_id', 'path', 'sort_order', 'is_active')
                    ->whereIn('apartment_id', $ids)
                    ->where('is_active', 1)
                    ->orderBy('apartment_id')
                    ->orderBy('sort_order')
                    ->get();

                $imagesByApt = collect($rows)->groupBy('apartment_id')->map(function ($rows) {
                    return collect($rows)->pluck('path')->map(function ($p) {
                        $p = is_string($p) ? trim($p) : '';
                        if ($p === '') return null;
                        return str_starts_with($p, 'http') ? $p : asset($p);
                    })->filter()->values();
                });
            }

            // 3) Mapear formato para la vista
            $properties = $apartments->map(function ($apt) use ($imagesByApt) {
                $id   = $apt['id'] ?? null;
                $name = trim($apt['name'] ?? 'Propiedad');

                $thumb = (function () use ($id) {
                    foreach (["images/smoobu/{$id}.webp", "images/smoobu/{$id}.jpg", "images/smoobu/{$id}.png"] as $rel) {
                        if ($id && file_exists(public_path($rel))) return asset($rel);
                    }
                    return asset('images/property-placeholder.jpg');
                })();

                $pictures = $imagesByApt->get($id, collect())->values()->all();

                return [
                    '_id'      => $id,
                    'title'    => $name,
                    'picture'  => ['thumbnail' => $thumb],
                    'pictures' => $pictures,
                    'gallery'  => $pictures,
                    'images'   => $pictures,
                ];
            })->values()->all();

            Log::info('[AboutController] properties construidas', ['count' => count($properties)]);

            // 4) Contenido editable desde about_pages
            //    Prioriza activa; si no hay, toma la primera; si tampoco existe, instancia en memoria.
            $about = AboutPage::active()->first()
                ?? AboutPage::first()
                ?? new AboutPage();

            return view('about', [
                'properties' => $properties,
                'about'  => $about, // <-- la vista ya espera $contenido
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en AboutController@index', ['e' => $e->getMessage()]);

            // Garantiza que la vista tenga $contenido aunque haya error
            $about = AboutPage::active()->first()
                ?? AboutPage::first()
                ?? new AboutPage();

            return view('about', [
                'properties' => [],
                'about'  => $about,
            ]);
        }
    }

    public function contact()
    {
        $contact = ContactPage::first();

        if (!$contact) {
            $contact = new ContactPage([
                'h1'          => 'Contacto',
                'h2_1'        => 'Estamos para ayudarte',
                'intro_text'  => 'Cuéntanos en qué podemos ayudarte y te respondemos pronto.',
                'is_active'   => true,
            ]);
        }

        $heroUrl   = $contact->hero_image   ? asset('images/' . $contact->hero_image)   : null;
        $bannerUrl = $contact->banner_image ? asset('images/' . $contact->banner_image) : null;

        return view('contacto', [
            'contact'   => $contact,
            'heroUrl'   => $heroUrl,
            'bannerUrl' => $bannerUrl,
        ]);
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'email', 'max:150'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'subject'     => ['nullable', 'string', 'max:150'],
            'message'     => ['required', 'string', 'max:4000'],
            'apartment_id'=> ['nullable', 'integer'],
            'checkin'     => ['nullable', 'date'],
            'checkout'    => ['nullable', 'date', 'after_or_equal:checkin'],
        ], [
            'name.required'    => 'Por favor indica tu nombre.',
            'email.required'   => 'Necesitamos tu correo para responderte.',
            'email.email'      => 'El correo no parece válido.',
            'message.required' => 'Cuéntanos en qué podemos ayudarte.',
            'checkout.after_or_equal' => 'La fecha de salida debe ser igual o posterior a la llegada.',
        ]);

        $to = config('mail.to.contact', config('mail.from.address', 'admin@chuspombo.com'));

        $lines = [
            '<h2>Nuevo mensaje desde Contacto</h2>',
            '<p><strong>Nombre:</strong> ' . e($data['name']) . '</p>',
            '<p><strong>Email:</strong> ' . e($data['email']) . '</p>',
            '<p><strong>Teléfono:</strong> ' . e($data['phone'] ?? '—') . '</p>',
            '<p><strong>Asunto:</strong> ' . e($data['subject'] ?? 'Contacto') . '</p>',
        ];

        if (!empty($data['apartment_id'])) {
            $lines[] = '<p><strong>Apartamento:</strong> ' . e($data['apartment_id']) . '</p>';
        }
        if (!empty($data['checkin']) || !empty($data['checkout'])) {
            $lines[] = '<p><strong>Fechas:</strong> ' . e($data['checkin'] ?? '—') . ' → ' . e($data['checkout'] ?? '—') . '</p>';
        }

        $lines[] = '<hr>';
        $lines[] = '<p style="white-space:pre-wrap;"><strong>Mensaje:</strong><br>' . nl2br(e($data['message'])) . '</p>';

        $html = implode('', $lines);

        try {
            Mail::send([], [], function ($message) use ($to, $data, $html) {
                $subject = 'Contacto Chuspombo — ' . ($data['subject'] ?? 'Nuevo mensaje');
                $message
                    ->to($to)
                    ->replyTo($data['email'], $data['name'])
                    ->subject($subject)
                    ->setBody($html, 'text/html');
            });

            Log::info('Contacto enviado', ['from' => $data['email'], 'name' => $data['name']]);

            return back()->with('success', '¡Gracias! Tu mensaje ha sido enviado. Te responderemos pronto.');
        } catch (\Throwable $e) {
            Log::error('Error enviando contacto', ['error' => $e->getMessage(), 'payload' => $data]);
            return back()->withInput()->with('error', 'No se pudo enviar el mensaje en este momento. Intenta de nuevo más tarde.');
        }
    }
}
