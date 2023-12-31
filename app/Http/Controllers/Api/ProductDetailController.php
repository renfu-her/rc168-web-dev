<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Product\ProductDetailService as Service;
class ProductDetailController extends Controller
{
    // index
    public function detail(Request $request)
    {
        return (new Service($request))
                ->detail()
                ->getResponse();
    }
    
}