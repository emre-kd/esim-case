<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/esim', function () {
    return Inertia::render('EsimDashboard');
});


Route::post('/payment', function (\Illuminate\Http\Request $request) {
    return Inertia::render('Payment', [
        'cart' => $request->input('cart'),
        'totalItems' => $request->input('totalItems'),
        'totalAmount' => $request->input('totalAmount'),
    ]);
});


Route::get('/payment', function (Request $request) {
    $cart = $request->session()->get('cart', []);
    $totalItems = $request->session()->get('totalItems', 0);
    $totalAmount = $request->session()->get('totalAmount', '0.00');

    return Inertia::render('Payment', [
        'cart' => $cart,
        'totalItems' => $totalItems,
        'totalAmount' => $totalAmount,
    ]);
})->name('payment.show');

Route::get('/payment/verify', function (Request $request) {
    $pendingEsimIds = $request->query('pendingEsimIds', []);
    $pendingEsimIds = array_map('intval', (array) $pendingEsimIds);

    \Log::info('Verification page props', ['pendingEsimIds' => $pendingEsimIds]);

    return Inertia::render('PaymentVerify', [
        'pendingEsimIds' => $pendingEsimIds,
    ]);
})->name('payment.verify');

Route::get('/payment/qr-codes', function () {
    return Inertia::render('QrCodes', [
        'sales' => request()->input('sales', []),
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
