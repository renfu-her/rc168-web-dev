<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{

    public function url(Request $request)
    {
        $data = $request->all();

        $test = $data['url'];

        dd(urlencode($test));
    }

}
