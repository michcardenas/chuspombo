<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ContactPage;
class AboutController extends Controller
{
    protected $smoobu;

    // Ajusta el tipo del servicio a tu clase real (p.ej. \App\Services\SmoobuApi)
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
                return $this->smoobu->apartments(); // ej: [['id'=>..., 'name'=>...], ...]
            });

            $apartments = collect($apartments ?? []);

            // 2) Opcional: leer imágenes por apartment desde la tabla (si la tienes)
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
                        // Si es relativo, vuelve URL absoluta
                        return str_starts_with($p, 'http') ? $p : asset($p);
                    })->filter()->values();
                });
            }

            // 3) Mapear al formato que la vista espera
            $properties = $apartments->map(function ($apt) use ($imagesByApt) {
                $id   = $apt['id'] ?? null;
                $name = trim($apt['name'] ?? 'Propiedad');

                // thumbnail local rápido (no imprescindible para el pool, pero útil)
                $thumb = (function () use ($id) {
                    foreach (["images/smoobu/{$id}.webp", "images/smoobu/{$id}.jpg", "images/smoobu/{$id}.png"] as $rel) {
                        if ($id && file_exists(public_path($rel))) return asset($rel);
                    }
                    return asset('images/property-placeholder.jpg');
                })();

                // Lista de imágenes opcionales para que la vista también encuentre por array
                $pictures = $imagesByApt->get($id, collect())->values()->all();

                return [
                    '_id'     => $id,
                    'title'   => $name,
                    'picture' => ['thumbnail' => $thumb],
                    // Estas claves ayudan a la vista a encontrar más URLs además del escaneo local:
                    'pictures' => $pictures, // <- importante si quieres reforzar el pool
                    'gallery'  => $pictures,
                    'images'   => $pictures,
                ];
            })->values()->all();

            Log::info('[AboutController] properties construidas', ['count' => count($properties)]);

            return view('about', [
                'properties' => $properties,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en AboutController@index', ['e' => $e->getMessage()]);
            return view('about', [
                'properties' => [],
            ]);
        }
    }

    public function contact()
    {
        // Cargamos el registro único de contacto
        // (si aún no existe, creamos uno en memoria con valores por defecto para no romper la vista)
        $contact = ContactPage::first();

        if (!$contact) {
            $contact = new ContactPage([
                'h1'          => 'Contacto',
                'h2_1'        => 'Estamos para ayudarte',
                'intro_text'  => 'Cuéntanos en qué podemos ayudarte y te respondemos pronto.',
                'is_active'   => true,
            ]);
        }

        // Si necesitas URLs listas para las imágenes en la vista, puedes prepararlas aquí:
        $heroUrl   = $contact->hero_image   ? asset('images/' . $contact->hero_image)   : null;
        $bannerUrl = $contact->banner_image ? asset('images/' . $contact->banner_image) : null;

        return view('contacto', [
            'contact'   => $contact,
            'heroUrl'   => $heroUrl,
            'bannerUrl' => $bannerUrl,
        ]);
    }

    /**
     * Procesa el formulario de contacto (POST)
     */
    public function contactSubmit(Request $request)
    {
        // Valida datos del formulario
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'email', 'max:150'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'subject'     => ['nullable', 'string', 'max:150'],
            'message'     => ['required', 'string', 'max:4000'],
            // Opcionales si incluyes campos en tu formulario
            'apartment_id' => ['nullable', 'integer'],
            'checkin'     => ['nullable', 'date'],
            'checkout'    => ['nullable', 'date', 'after_or_equal:checkin'],
        ], [
            'name.required'    => 'Por favor indica tu nombre.',
            'email.required'   => 'Necesitamos tu correo para responderte.',
            'email.email'      => 'El correo no parece válido.',
            'message.required' => 'Cuéntanos en qué podemos ayudarte.',
            'checkout.after_or_equal' => 'La fecha de salida debe ser igual o posterior a la llegada.',
        ]);

        // A quién enviamos (usa .env si lo tienes, sino fallback)
        $to = config('mail.to.contact', config('mail.from.address', 'admin@chuspombo.com'));

        // Construimos un HTML sencillo para el correo
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
            // Envío simple sin crear Mailable (válido y rápido)
            Mail::send([], [], function ($message) use ($to, $data, $html) {
                $subject = 'Contacto Chuspombo — ' . ($data['subject'] ?? 'Nuevo mensaje');
                $message
                    ->to($to)
                    ->replyTo($data['email'], $data['name'])
                    ->subject($subject)
                    ->setBody($html, 'text/html');
            });

            // Si Mail no está configurado, Mail::failures puede estar vacío,
            // pero igual dejamos log de éxito por trazabilidad
            Log::info('Contacto enviado', ['from' => $data['email'], 'name' => $data['name']]);

            return back()->with('success', '¡Gracias! Tu mensaje ha sido enviado. Te responderemos pronto.');
        } catch (\Throwable $e) {
            // Si falla el correo, lo registramos y devolvemos un mensaje amable
            Log::error('Error enviando contacto', ['error' => $e->getMessage(), 'payload' => $data]);

            // Puedes decidir si quieres continuar sin error crítico:
            return back()->withInput()->with('error', 'No se pudo enviar el mensaje en este momento. Intenta de nuevo más tarde.');
        }
    }
}
