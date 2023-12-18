<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BonusService as Service;

class BonusController extends Controller
{
    // index
    public function index(Request $request)
    {
        return (new Service($request))
            ->list()
            ->getResponse();
    }

    // store
    public function store(Request $request)
    {
        return (new Service($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
}
