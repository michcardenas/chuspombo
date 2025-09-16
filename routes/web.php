<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaginaController;
use App\Http\Controllers\Admin\SmoobuApartmentController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Páginas estáticas
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/services', [HomeController::class, 'services'])->name('services');
Route::get('/for-owners', [HomeController::class, 'forOwners'])->name('for-owners');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');

// Propiedades

Route::get('/propiedades', [PropertiesController::class, 'index'])->name('properties.index');
Route::get('/propiedades/{id}', [PropertiesController::class, 'show'])->name('properties.show');
Route::get('/propiedades/{id}/reservar', [PropertiesController::class, 'showReservationForm'])->name('properties.reservation');
Route::post('/propiedades/{id}/reservar', [PropertiesController::class, 'createReservation'])->name('properties.reserve');

//Cotizar
Route::post('/properties/calculate-price', [PropertiesController::class, 'calculatePrice'])->name('properties.calculatePrice');
Route::post('/properties/create-quote', [PropertiesController::class, 'createQuote'])->name('properties.createQuote');
Route::post('/properties/redirect-to-portal', [PropertiesController::class, 'redirectToGuestPortal'])->name('properties.redirect-to-portal');
Route::get('/booking/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');


// Reservas
Route::get('/book/{propertyId}', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/book/{propertyId}', [ReservationController::class, 'store'])->name('reservations.store');

// about
Route::get('/nosotros', [App\Http\Controllers\AboutController::class, 'index'])->name('about');
Route::get('/contacto', [App\Http\Controllers\AboutController::class, 'contact'])->name('contact');

Route::post('/landing/contact', [App\Http\Controllers\AboutController::class, 'contactSubmit'])->name('contact.submit');



Route::post('/properties/{id}/confirm-reservation', [PropertiesController::class, 'confirmReservation'])->name('properties.confirm-reservation');
Route::post('/properties/process-reservation', [PropertiesController::class, 'processReservation'])->name('properties.process-reservation');
Route::get('/properties/redirect-to-portal', [PropertiesController::class, 'redirectToPortal'])->name('properties.redirect-to-portal');
Route::post('/properties/{propertyId}/payment-form', [PropertiesController::class, 'paymentForm'])->name('properties.payment-form');
Route::post('properties/payment-form/{propertyId}/{quoteId}', [PropertiesController::class, 'showPaymentForm'])->name('properties.payment-form');
Route::post('/properties/{id}/tokenize-card', [PropertiesController::class, 'tokenizeCard'])->name('properties.tokenize-card');
Route::post('/properties/process-payment/{id}', [PropertiesController::class, 'processPayment'])->name('properties.process-payment');
//Admin
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::middleware('auth')->put('/pagina/{id}', [PaginaController::class, 'update'])->name('admin.pagina.update');
Route::middleware('auth')->get('/admin/pagina/propiedades', [PaginaController::class, 'showPropiedades'])->name('admin.pagina.propiedades');

Route::middleware('auth')->get('/admin/pagina/propiedades/edit', [PaginaController::class, 'editPropiedades'])->name('admin.pagina.propiedades.edit');
Route::middleware('auth')->post('/admin/pagina/propiedades/update', [PaginaController::class, 'updatePropiedades'])->name('admin.pagina.propiedades.update');

Route::get('/admin/pagina/contacto/edit', [\App\Http\Controllers\Admin\PaginaController::class, 'editContacto'])
    ->name('admin.pagina.contacto.edit');

Route::post('/admin/pagina/contacto', [\App\Http\Controllers\Admin\PaginaController::class, 'updateContacto'])
    ->name('admin.pagina.contacto.update');


    /* ====== NUEVO: Nosotros (About) ====== */
Route::middleware('auth')->get('/admin/pagina/nosotros/edit', [PaginaController::class, 'editNosotros'])
    ->name('admin.pagina.nosotros.edit');

Route::middleware('auth')->post('/admin/pagina/nosotros', [PaginaController::class, 'updateNosotros'])
    ->name('admin.pagina.nosotros.update');

Route::middleware(['auth'])->prefix('admin/apartments')->name('admin.apartments.')->group(function () {
    Route::get('{apartment}/edit', [SmoobuApartmentController::class, 'edit'])->name('edit');
    Route::post('{apartment}', [SmoobuApartmentController::class, 'update'])->name('update');
    Route::delete('images/{image}', [SmoobuApartmentController::class, 'destroyImage'])->name('images.destroy');
});
Route::post('/properties/check-availability', [\App\Http\Controllers\PropertiesController::class, 'checkAvailability'])
    ->name('properties.checkAvailability');

Route::post('/stripe/pagar', [StripeController::class, 'pagar'])->name('stripe.pagar');
Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/stripe/error', [StripeController::class, 'error'])->name('payments.failure');
Route::get('/stripe/redirect', [StripeController::class, 'handleRedirect'])->name('stripe.redirect');

Route::prefix('admin/pagos')->name('admin.pagos.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\PagoController::class, 'index'])->name('index');
});

Route::post('/paypal/pay', [PayPalController::class, 'pay'])->name('paypal.pay');
Route::get('/paypal/success', [PayPalController::class, 'success'])->name('paypal.success');
Route::get('/paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');

Route::get('/checkout/availability', [CheckoutController::class, 'availability'])->name('checkout.availability'); // disponibilidad (AJAX)
Route::post('/checkout/quote', [CheckoutController::class, 'quote'])->name('checkout.quote');                     // cotizar (AJAX)
Route::post('/checkout/start', [CheckoutController::class, 'start'])->name('checkout.start');                     // ir a resumen
Route::get('/checkout/summary', [CheckoutController::class, 'summary'])->name('checkout.summary');                // resumen


Route::get('/pago/exito/{id}', function($id) {
    $payment = \App\Models\PropertyPayment::findOrFail($id);
    return view('payments.success', compact('payment'));
})->name('pago.exito');

Route::get('/pago/fallo', function() {
    return view('payments.failure');
})->name('pago.fallo');