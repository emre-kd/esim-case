<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/get/countries', function () {
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'token' => '7c57f824afbac3bd4c60c60cd27eca35',
    ])->get('https://api-test.tamamliyo.com/partner/v1/esim/countries');

    return $response->json();
});


Route::get('/get/country/coverages/{ulkeKodu}', function ($ulkeKodu) {
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'token' => '7c57f824afbac3bd4c60c60cd27eca35',
    ])->get("https://api-test.tamamliyo.com/partner/v1/esim/coverages/{$ulkeKodu}");

    return $response->json();
});
