<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CoinService as Service;
class CoinController extends Controller
{
    // index
    public function index(Request $request)
    {
        return (new Service($request))
                ->index()
                ->getResponse();
    }
    
}