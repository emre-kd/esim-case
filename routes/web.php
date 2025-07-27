<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaymentController;

// Ana Sayfa
Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

// Dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
});

// eSIM Dashboard
Route::get('/esim', fn () => Inertia::render('EsimDashboard'))->name('esim.dashboard');

// Payment Routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    Route::get('/', [PaymentController::class, 'show'])->name('show');
    Route::get('/verify', [PaymentController::class, 'verify'])->name('verify');
    Route::get('/qr-codes', [PaymentController::class, 'qrCodes'])->name('qr');
});

// Diğer route dosyaları
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
