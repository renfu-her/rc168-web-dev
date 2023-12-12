<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class SendMailController extends Controller
{
    // index
    public function send(Request $request)
    {
        $data = $request->all();

        Mail::send([], [], function ($message) use ($data) {
            $message
                ->from($data['from']['email'], $data['from']['name'])
                ->to($data['to']['email'], $data['to']['name'])
                ->replyTo($data['reply_to']['email'], $data['reply_to']['name'])
                ->subject($data['subject'])
                ->html($data['body']);
        });

        return '';
    }
}
