<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Product\ProductDetailService as Service;

class ProductDetailController extends Controller
{
    // index
    public function detail(Request $request, $id)
    {

        return (new Service($request, $id))
            ->detail()
            ->getResponse();
    }

    //TODO: 抓取內容
    public function getContent(Request $request, $id)
    {
        return (new Service($request, $id))
            ->getContent()
            ->getResponse();
    }

    //TODO: 發送 order
    public function setOrder(Request $request, $customerId)
    {
        return (new Service($request, $customerId))
            ->setOrder($customerId)
            ->getResponse();
    }

}
