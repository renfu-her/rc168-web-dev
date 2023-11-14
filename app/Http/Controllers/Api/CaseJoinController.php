<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Work\CaseJoinService as Service;

class CaseJoinController extends Controller
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

    // view
    public function view(Request $request)
    {
        return (new Service($request))
            ->runValidate('view')
            ->view()
            ->getResponse();
    }

    // public function update(Request $request, $id)
    // {
    //     return (new Service($request, $id))
    //             ->runValidate('update')
    //             ->update()
    //             ->getResponse();
    // }
    // // destroy
    // public function destroy(Request $request, $id)
    // {
    //     return (new Service($request, $id))
    //             ->destroy()
    //             ->getResponse();
    // }
}
