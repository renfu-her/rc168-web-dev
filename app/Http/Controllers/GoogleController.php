<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoogleService as Service;
class GoogleController extends Controller
{
    // index
    public function drive(Request $request)
    {
        return (new Service($request))
                ->list();
    }
    
}