<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QrcodeController extends Controller
{
    public function qrcode()
    {
        return view('qrcode');
    }
}
