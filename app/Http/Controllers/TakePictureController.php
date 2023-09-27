<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TakePictureController extends Controller
{

    public function takePicture()
    {
        return view('take-picture');
    }
}
