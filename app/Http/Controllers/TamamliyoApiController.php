<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TamamliyoApiController extends Controller
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.tamamliyo.base_url');
        $this->token = config('services.tamamliyo.token');
    }

    protected function client()
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'token' => $this->token,
        ]);
    }

    public function getCountries()
    {
        $response = $this->client()->get("{$this->baseUrl}/countries");
        return $response->json();
    }

    public function getCoverages(string $countryCode)
    {
        $response = $this->client()->get("{$this->baseUrl}/coverages/{$countryCode}");
        return $response->json();
    }

    public function createEsim(Request $request)
    {
        $response = $this->client()->post("{$this->baseUrl}/create", $request->all());
        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type'));
    }

    public function confirmEsim(Request $request)
    {
        $response = $this->client()->post("{$this->baseUrl}/confirm", $request->all());
        return $response->json();
    }
}
