<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function content(Request $request, $product_id)
    {

        $res = Http::withoutVerifying()->get(config('app.url') . '/api/product/detail/content/' . $product_id);

        $result = $res->body();

        $result = json_decode($result, true);

        dd($result);
        
        return view('productContent', compact('result'));
    }
}
