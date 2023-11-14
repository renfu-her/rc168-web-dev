<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Work\JoinWriteService as Service;
class JoinWriteController extends Controller
{
    
    // store
    public function store(Request $request)
    {
        return (new Service($request))
                ->runValidate('store')
                ->store()
                ->getResponse();
    }
    // // update
    // public function update(Request $request, $id)
    // {
    //     return (new Service($request, $id))
    //             ->runValidate('update')
    //             ->update()
    //             ->getResponse();
    // }
    
}