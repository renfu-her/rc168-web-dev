<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Bonus\UserBonusService as Service;
class UserBonusController extends Controller
{
    // save
    public function save(Request $request)
    {
        return (new Service($request))
                ->save()
                ->getResponse();
    }

    // get all
    public function getAll(Request $request)
    {
        return (new Service($request))
                ->getAll()
                ->getResponse();
    }
    
}