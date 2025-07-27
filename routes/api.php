<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TamamliyoApiController;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());

Route::prefix('tamamliyo')->name('tamamliyo.')->group(function () {
    Route::get('/countries', [TamamliyoApiController::class, 'getCountries'])->name('countries');
    Route::get('/country/coverages/{countryCode}', [TamamliyoApiController::class, 'getCoverages'])->name('coverages');
    Route::post('/esim-create', [TamamliyoApiController::class, 'createEsim'])->name('esim.create');
    Route::post('/esim-confirm', [TamamliyoApiController::class, 'confirmEsim'])->name('esim.confirm');
});
