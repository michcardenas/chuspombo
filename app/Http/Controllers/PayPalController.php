<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\PayPalService;
use App\Services\SmoobuClient;
use App\Models\PropertyPayment;
use App\Models\PaypalCheckoutSession;

class PayPalController extends Controller
{
    public function __construct(
        private PayPalService $paypal,
        private SmoobuClient $smoobu
    ) {}

    public function pay(Request $request)
    {
        $checkout = Session::get('checkout');
        if (!$checkout) {
            return redirect()->route('checkout.summary')
                ->withErrors(['checkout' => 'No hay datos de checkout.']);
        }

        $amount   = number_format($checkout['total'], 2, '.', '');
        $currency = $checkout['currency'] ?? 'EUR';

        // NO mandamos el JSON en custom_id; lo respaldamos por ORDER_ID
        $order = $this->paypal->createOrder(
            amount: $amount,
            currency: $currency,
            customId: null,
            description: $checkout['apartment_title'] ?? 'Reserva'
        );

        $orderId = $order->result->id ?? null;

        if ($orderId) {
            // Guardar respaldo completo del checkout en la base de datos
            PaypalCheckoutSession::store($orderId, $checkout);
            Log::debug('PayPal order created', ['order_id' => $orderId, 'amount' => $amount, 'currency' => $currency]);
        }

        if (!empty($order->result->links)) {
            foreach ($order->result->links as $link) {
                if ($link->rel === 'approve') {
                    return redirect()->away($link->href);
                }
            }
        }

        return back()->with('error', 'No se pudo iniciar el pago con PayPal.');
    }

    public function success(Request $request)
    {
        $orderId = $request->query('token'); // PayPal retorna ?token=ORDER_ID
        if (!$orderId) {
            return redirect()->route('checkout.summary')->with('error', 'Orden de PayPal inválida.');
        }

        $result = $this->paypal->captureOrder($orderId);
        Log::debug('PayPal capture payload', ['order_id' => $orderId, 'payload' => json_encode($result)]);

        // ---- Reconstrucción de datos de reserva ----
        $customData = [];

        Log::debug('Starting data recovery for PayPal order', ['order_id' => $orderId]);

        // 1) Intentar leer custom_id (por si en el futuro envías un token corto ahí)
        $rawCustom = $result->result->purchase_units[0]->custom_id ?? null;
        Log::debug('PayPal custom_id check', ['raw_custom' => $rawCustom]);

        if ($rawCustom) {
            $decoded = json_decode($rawCustom, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $customData = $decoded;
                Log::debug('Data recovered from custom_id', ['data' => $customData]);
            }
        }

        // 2) Fallback por base de datos usando ORDER_ID
        if (empty($customData)) {
            $chk = PaypalCheckoutSession::retrieve($orderId);
            Log::debug('Database lookup result', ['checkout_data' => $chk]);

            if ($chk) {
                $customData = [
                    'apt'      => $chk['apartment_id'] ?? null,
                    'ci'       => $chk['checkin'] ?? null,
                    'co'       => $chk['checkout'] ?? null,
                    'ci_hour'  => $chk['checkin_hour'] ?? '15:00',
                    'co_hour'  => $chk['checkout_hour'] ?? '11:00',
                    'nights'   => $chk['nights'] ?? null,
                    'guests'   => $chk['guests'] ?? null,
                    'total'    => $chk['total'] ?? 0,
                    'currency' => $chk['currency'] ?? 'EUR',
                    'title'    => $chk['apartment_title'] ?? 'Reserva',
                ];
                Log::debug('Checkout data recovered from database', ['order_id' => $orderId, 'data' => $customData]);
            } else {
                Log::warning('No checkout data found in database for order', ['order_id' => $orderId]);
            }
        }

        Log::debug('Final custom data for processing', ['custom_data' => $customData]);

        if (empty($customData) || empty($customData['apt'])) {
            Log::error('Datos de reservación no encontrados tras captura', [
                'paypal_order' => $orderId,
                'result_payload' => json_encode($result->result),
                'session_data' => Session::get('checkout'),
                'db_data' => PaypalCheckoutSession::retrieve($orderId)
            ]);
            return view('checkout.success', [
                'order' => $result->result,
                'booking_error' => true,
                'error_message' => 'El pago se procesó correctamente, pero no pudimos recuperar los datos de la reservación. Te contactaremos para finalizarla.',
            ]);
        }

        // ---- Payer info desde PayPal ----
        $payer      = $result->result->payer ?? null;
        $guestEmail = $payer->email_address ?? null;
        $guestName  = '';
        $guestPhone = '';

        if (isset($payer->name)) {
            $nameParts = [];
            if (isset($payer->name->given_name))  $nameParts[] = $payer->name->given_name;
            if (isset($payer->name->middle_name)) $nameParts[] = $payer->name->middle_name;
            if (isset($payer->name->surname))     $nameParts[] = $payer->name->surname;
            $guestName = trim(implode(' ', $nameParts));
        }

        // Mejorar extracción de teléfono con múltiples opciones
        $guestPhone = $this->extractPhoneFromPayer($payer);
        Log::debug('Phone extraction result', [
            'paypal_order' => $orderId,
            'payer_data' => $payer ? json_encode($payer) : null,
            'extracted_phone' => $guestPhone
        ]);

        // ---- Validar datos antes de registrar en BD ----
        if (empty($customData['apt']) || empty($customData['total']) || empty($customData['ci']) || empty($customData['co'])) {
            Log::error('Datos insuficientes para crear pago', [
                'paypal_order' => $orderId,
                'custom_data' => $customData,
                'missing_fields' => [
                    'apartment_id' => empty($customData['apt']),
                    'total' => empty($customData['total']),
                    'checkin' => empty($customData['ci']),
                    'checkout' => empty($customData['co'])
                ]
            ]);

            return view('checkout.success', [
                'order' => $result->result,
                'booking_error' => true,
                'error_message' => 'El pago se procesó correctamente, pero faltan datos de la reservación. Te contactaremos para completar el proceso.',
            ]);
        }

        // ---- Registrar pago en BD antes de crear reserva Smoobu ----
        $payment = PropertyPayment::create([
            'apartment_id' => (int)$customData['apt'],
            'amount'       => (float)$customData['total'],
            'currency'     => $customData['currency'] ?? 'EUR',
            'payment_method' => 'paypal',
            'payment_id'   => $orderId,
            'status'       => 'completed', // capturado
            'payment_status' => 'completed', // campo legacy para compatibilidad
            'checkin'      => $customData['ci'],
            'checkout'     => $customData['co'],
            'guests'       => (int)($customData['guests'] ?? 1),
            'nights'       => (int)($customData['nights'] ?? 1),
            'guest_name'   => $guestName ?: 'Cliente PayPal',
            'guest_email'  => $guestEmail ?: 'noreply@paypal.com',
            'guest_phone'  => $guestPhone,
        ]);

        // ---- Intentar crear reserva en Smoobu ----
        $smoobuReservation = null;
        try {
            $nameParts = explode(' ', $guestName, 2);
            $firstName = $nameParts[0] ?: 'Cliente';
            $lastName  = $nameParts[1] ?? 'PayPal';

            // Preparar datos completos para Smoobu API
            $bookingData = [
                // Datos básicos de la reservación (REQUERIDOS)
                'apartmentId'  => (int)$customData['apt'],
                'arrivalDate'  => $customData['ci'],
                'departureDate'=> $customData['co'],
                'adults'       => (int)$customData['guests'],
                'children'     => 0,

                // Información del huésped (REQUERIDOS)
                'firstName'   => $firstName,
                'lastName'    => $lastName,
                'email'       => $guestEmail ?: 'noreply@paypal.com',
                'phone'       => $guestPhone ?: '',

                // Información de pricing (OPCIONAL pero recomendado)
                'price'       => (float)$customData['total'],
                'currency'    => $customData['currency'] ?? 'EUR',

                // Información de la fuente (OPCIONAL)
                'source'      => 'Website',
                'channel'     => 'PayPal',

                // Notas internas (OPCIONAL)
                'notice'      => 'Reserva automática desde website' . "\n" .
                                'PayPal ID: ' . $orderId . "\n" .
                                (!empty($payer->payer_id) ? ('PayPal User: ' . $payer->payer_id . "\n") : '') .
                                'Check-in: ' . ($customData['ci_hour'] ?? '15:00') . "\n" .
                                'Check-out: ' . ($customData['co_hour'] ?? '11:00') . "\n" .
                                'Huéspedes: ' . $customData['guests'] . "\n" .
                                'Precio total: €' . $customData['total'],

                // Configuraciones adicionales (OPCIONAL)
                'assignmentMode' => 'auto',
                'language'    => 'es',
                'guestCountry' => 'ES',
                'salutation'  => '',
                'comment'     => 'Reservación procesada automáticamente via PayPal',

                // Estado de la reservación
                'prepayment'  => (float)$customData['total'],
                'prepaymentDate' => now()->toDateString(),
            ];

            Log::info('Attempting to create Smoobu booking', [
                'paypal_order' => $orderId,
                'booking_data' => $bookingData
            ]);

            $smoobuReservation = $this->smoobu->createBooking($bookingData);

            Log::info('Smoobu API response', [
                'paypal_order' => $orderId,
                'response' => $smoobuReservation
            ]);

            if (isset($smoobuReservation['id']) && !empty($smoobuReservation['id'])) {
                $payment->update(['reservation_id' => $smoobuReservation['id']]);
                Log::info('Reservación creada exitosamente en Smoobu', [
                    'reservation_id' => $smoobuReservation['id'],
                    'paypal_order'   => $orderId,
                    'guest_name'     => $guestName,
                    'apartment_id'   => $customData['apt'],
                    'dates'          => $customData['ci'] . ' to ' . $customData['co']
                ]);
            } else {
                // Si no hay ID en la respuesta, marcar para revisión manual
                $payment->update(['status' => 'requires_manual_booking']);
                Log::warning('Respuesta de Smoobu sin ID de reservación', [
                    'paypal_order' => $orderId,
                    'smoobu_response' => $smoobuReservation,
                    'booking_data_sent' => $bookingData
                ]);
            }
        } catch (\Throwable $e) {
            $payment->update(['status' => 'requires_manual_booking']);
            Log::error('Error creando reservación en Smoobu', [
                'paypal_order' => $orderId,
                'error'        => $e->getMessage()
            ]);
            return view('checkout.success', [
                'order' => $result->result,
                'payment' => $payment,
                'booking_error' => true,
                'error_message' => 'El pago se procesó correctamente, pero hubo un problema creando la reservación. Nos pondremos en contacto contigo.',
            ]);
        } finally {
            // Limpieza de sesión y base de datos
            Session::forget('checkout');
            PaypalCheckoutSession::where('paypal_order_id', $orderId)->delete();

            // Limpieza programada de sesiones antiguas
            PaypalCheckoutSession::cleanup();
        }

        return view('checkout.success', [
            'order' => $result->result,
            'payment' => $payment,
            'reservation' => $smoobuReservation
        ]);
    }

    public function cancel()
    {
        return redirect()->route('checkout.summary')->with('error', 'Pago cancelado.');
    }

    /**
     * Extraer número de teléfono del payer de PayPal con múltiples opciones
     */
    private function extractPhoneFromPayer($payer): string
    {
        if (!$payer) return '';

        // Opción 1: phone.phone_number.national_number (formato estándar)
        if (isset($payer->phone->phone_number->national_number)) {
            return $payer->phone->phone_number->national_number;
        }

        // Opción 2: phone.phone_number.international_number
        if (isset($payer->phone->phone_number->international_number)) {
            return $payer->phone->phone_number->international_number;
        }

        // Opción 3: phone.phone_number (si es string directo)
        if (isset($payer->phone->phone_number) && is_string($payer->phone->phone_number)) {
            return $payer->phone->phone_number;
        }

        // Opción 4: phone (si es string directo)
        if (isset($payer->phone) && is_string($payer->phone)) {
            return $payer->phone;
        }

        // Opción 5: Buscar en address.phone si existe
        if (isset($payer->address->phone)) {
            return $payer->address->phone;
        }

        // Opción 6: Buscar en shipping.phone si existe
        if (isset($payer->shipping->phone)) {
            return $payer->shipping->phone;
        }

        return '';
    }
}
