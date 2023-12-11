<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    // index
    public function index(Request $request)
    {
        $data = [
            'content' => '測試',
        ];

        return Mail::send('emails.test', $data, function ($message) use ($data) {
            $message->to('renfu.her@gmail.com')->subject('測試');
        });
    }
}
