<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EcpayController extends Controller
{
    // index
    public function index(Request $request)
    {
        $res = Http::withoutVerifying()->post('https://api-dev.besttour.com.tw/api/payment/ecpay', [
            "item_description" => "KWL06BR91202A-476002團費",
            "item_name" => "KWL06BR91202A-476002團費",
            "order_no" => "470" . date('YmdHis'),
            "total_amount" => 100,
        ]);

        $result = $res->json();

        $resArray = $result['data'];

        return view('ecpay', compact('resArray'));
    }
}
