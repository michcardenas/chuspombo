<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\PropertyPayment;

class StripeController extends Controller
{
    /**
     * Procesar el pago con Stripe
     */
    public function pagar(Request $request)
    {
        try {
            // Validación de campos necesarios
            $validated = $request->validate([
                'source_id'     => 'required|string',
                'amount'        => 'required|integer',
                'currency'      => 'required|string',
                'listing_id'    => 'required|string',
                'quote_id'      => 'required|string',
                'check_in'      => 'required|date',
                'check_out'     => 'required|date',
                'guests_count'  => 'required|integer|min:1',
                'guest_name'    => 'required|string',
                'guest_email'   => 'required|email',
                'guest_phone'   => 'nullable|string',
            ]);

            $stripeSecret = config('services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripeSecret);

            // Crear el cargo
            $charge = \Stripe\Charge::create([
                'amount' => $validated['amount'], // en centavos
                'currency' => $validated['currency'],
                'source' => $validated['source_id'],
                'description' => "Reserva en propiedad {$validated['listing_id']} (Quote: {$validated['quote_id']})",
                'metadata' => [
                    'check_in' => $validated['check_in'],
                    'check_out' => $validated['check_out'],
                    'guests' => $validated['guests_count'],
                    'guest_name' => $validated['guest_name'],
                    'guest_email' => $validated['guest_email'],
                    'guest_phone' => $validated['guest_phone'] ?? '',
                ],
                'receipt_email' => $validated['guest_email'],
            ]);

            // Redirigir a la página de éxito
            return response()->json(['redirect' => route('stripe.success')]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::error("Stripe Error: " . $e->getMessage());
            return response()->json(['error' => 'Tarjeta rechazada: ' . $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error("Error general: " . $e->getMessage());
            return response()->json(['error' => 'Error al procesar el pago. Inténtalo más tarde.'], 500);
        }
    }

    public function success()
    {
        return view('payments.success'); // ✅ esta sí existe
    }

    public function error()
    {
        return view('payments.failure'); // ✅ esta también existe
    }


    public function handleRedirect(Request $request)
    {
        try {
            $paymentIntentId = $request->input('payment_intent');
            $redirectStatus = $request->input('redirect_status');

            if (!$paymentIntentId) {
                return redirect()->route('payments.failure')->with('error', 'No se encontró el ID del pago.');
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            $intent = PaymentIntent::retrieve($paymentIntentId);
            $metadata = $intent->metadata;

            // Extraer detalles de la tarjeta si existen
            $cardBrand = null;
            $last4 = null;
            $paymentMethodType = $intent->payment_method_types[0] ?? null;

            if (!empty($intent->charges->data)) {
                $charge = $intent->charges->data[0];
                $cardDetails = $charge->payment_method_details->card ?? null;
                $cardBrand = $cardDetails->brand ?? null;
                $last4 = $cardDetails->last4 ?? null;
            }

            // Evitar duplicados
            $payment = PropertyPayment::where('stripe_payment_id', $paymentIntentId)->first();

            if (!$payment) {
                $payment = PropertyPayment::create([
                    'listing_id'          => $metadata->listing_id ?? null,
                    'quote_id'            => $metadata->quote_id ?? null,
                    'guest_name'          => $metadata->guest_name ?? null,
                    'guest_email'         => $metadata->guest_email ?? null,
                    'guest_phone'         => $metadata->guest_phone ?? null,
                    'check_in'            => $metadata->check_in ?? null,
                    'check_out'           => $metadata->check_out ?? null,
                    'guests_count'        => $metadata->guests_count ?? null,
                    'total_price'         => $intent->amount / 100,
                    'currency'            => $intent->currency,
                    'stripe_payment_id'   => $intent->id,
                    'payment_status'      => $intent->status,
                    'card_brand'          => $cardBrand,
                    'last_4'              => $last4,
                    'payment_method_type' => $paymentMethodType,
                ]);
            }

            if ($redirectStatus === 'succeeded' && $intent->status === 'succeeded') {
                return view('payments.success', compact('payment'));
            }

            $payment->update(['payment_status' => 'failed']);
            return redirect()->route('payments.failure')->with('error', 'El pago fue cancelado o falló.');
        } catch (\Exception $e) {
            Log::error('❌ Error en handleRedirect(): ' . $e->getMessage());
            return redirect()->route('payments.failure')->with('error', 'Ocurrió un error al procesar el pago.');
        }
    }
}
