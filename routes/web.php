<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
