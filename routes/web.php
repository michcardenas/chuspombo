<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaginaController;
use App\Http\Controllers\StripeController;



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

// Rutas de prueba para Guesty API
Route::prefix('guesty-test')->group(function () {
    Route::get('/base-urls', [App\Http\Controllers\GuestyTestController::class, 'testBaseUrl']);
    Route::get('/auth', [App\Http\Controllers\GuestyTestController::class, 'testAuthUrl']);
    Route::get('/token', [App\Http\Controllers\GuestyTestController::class, 'validateToken']);
});

// En routes/web.php
Route::prefix('token-test')->group(function () {
    Route::get('/status', [App\Http\Controllers\TokenTestController::class, 'testTokenSystem']);
    Route::get('/refresh', [App\Http\Controllers\TokenTestController::class, 'manualRefreshToken']);
});


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


Route::post('/stripe/pagar', [StripeController::class, 'pagar'])->name('stripe.pagar');
Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/stripe/error', [StripeController::class, 'error'])->name('payments.failure');
Route::get('/stripe/redirect', [StripeController::class, 'handleRedirect'])->name('stripe.redirect');

Route::prefix('admin/pagos')->name('admin.pagos.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\PagoController::class, 'index'])->name('index');
});

Route::get('/pago/exito/{id}', function($id) {
    $payment = \App\Models\PropertyPayment::findOrFail($id);
    return view('payments.success', compact('payment'));
})->name('pago.exito');

Route::get('/pago/fallo', function() {
    return view('payments.failure');
})->name('pago.fallo');