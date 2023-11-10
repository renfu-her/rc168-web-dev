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
            "user_id" => 123,
            "item_description" => "item description",
            "item_name" => "item name",
            "order_no" => "OID" . date('YmdHis'),
            "total_amount" => 100,
        ]);

        $result = $res->body();

        return view('ecpay', compact('result'));
    }
}
