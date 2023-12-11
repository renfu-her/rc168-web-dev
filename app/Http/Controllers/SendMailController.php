<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    // index
    public function send(Request $request)
    {
        $data = [
            'content' => '測試 ' . date('YmdHis'),
        ];

        Mail::send('emails.test', $data, function ($message) use ($data) {
            $message->to('renfu.her@gmail.com')->subject('測試 ' . date('YmdHis'));
        });

        return '';
    }
}
