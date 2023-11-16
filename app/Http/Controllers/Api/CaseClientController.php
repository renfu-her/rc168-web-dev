<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Work\CaseClientService as Service;

class CaseClientController extends Controller
{

    // store
    public function store(Request $request)
    {
        return (new Service($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    // view
    public function view(Request $request)
    {
        return (new Service($request))
            ->runValidate('view')
            ->view()
            ->getResponse();
    }
    // all view
    public function getAll(Request $request)
    {
        return (new Service($request))
            ->runValidate('getAll')
            ->getAll()
            ->getResponse();
    }

}
