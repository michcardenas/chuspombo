<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\PayPalService;

class PayPalController extends Controller
{
    public function __construct(private PayPalService $paypal) {}

    public function pay(Request $request)
    {
        $checkout = Session::get('checkout');
        if (!$checkout) {
            return redirect()->route('checkout.summary')->withErrors(['checkout' => 'No hay datos de checkout.']);
        }

        $amount   = number_format($checkout['total'], 2, '.', '');
        $currency = $checkout['currency'] ?? 'EUR';

        $customId = json_encode([
            'apt'    => $checkout['apartment_id'],
            'ci'     => $checkout['checkin'],
            'co'     => $checkout['checkout'],
            'nights' => $checkout['nights'],
            'guests' => $checkout['guests'],
        ]);

        $order = $this->paypal->createOrder(
            amount: $amount,
            currency: $currency,
            customId: $customId,
            description: $checkout['apartment_title']
        );

        foreach ($order->result->links as $link) {
            if ($link->rel === 'approve') {
                return redirect()->away($link->href);
            }
        }
        return back()->with('error', 'No se pudo iniciar el pago con PayPal.');
    }

    public function success(Request $request)
    {
        $orderId = $request->query('token'); // ?token=ORDER_ID
        $result  = $this->paypal->captureOrder($orderId);

        // TODO: aquí persistes la reserva con $result y los metadatos
        Session::forget('checkout');

        return view('checkout.success', ['order' => $result->result]);
    }

    public function cancel()
    {
        return redirect()->route('checkout.summary')->with('error', 'Pago cancelado.');
    }
}
