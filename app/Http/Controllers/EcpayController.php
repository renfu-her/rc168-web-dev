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
            "item_description" => "AAAAAAAAAAAAAKKKK-123團費",
            "item_name" => "AAAAAAAAAAAAAAAAAAKKKK-123團費",
            "order_no" => "43210" . date('YmdHis'),
            "total_amount" => 100,
        ]);

        $result = $res->json();

        $resArray = $result['data'];

        return view('ecpay', compact('resArray'));
    }
}
