<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        return Inertia::render('Payment', [
            'cart' => $request->input('cart'),
            'totalItems' => $request->input('totalItems'),
            'totalAmount' => $request->input('totalAmount'),
        ]);
    }

    public function show(Request $request)
    {
        return Inertia::render('Payment', [
            'cart' => $request->session()->get('cart', []),
            'totalItems' => $request->session()->get('totalItems', 0),
            'totalAmount' => $request->session()->get('totalAmount', '0.00'),
        ]);
    }

    public function verify(Request $request)
    {
        $pendingEsimIds = array_map('intval', (array) $request->query('pendingEsimIds', []));

        \Log::info('Verification page props', ['pendingEsimIds' => $pendingEsimIds]);

        return Inertia::render('PaymentVerify', [
            'pendingEsimIds' => $pendingEsimIds,
        ]);
    }

    public function qrCodes(Request $request)
    {
        return Inertia::render('QrCodes', [
            'sales' => $request->input('sales', []),
        ]);
    }
}
