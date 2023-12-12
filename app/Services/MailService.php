<?php

namespace App\Services;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;

class MailService extends Service
{
    use RulesTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function index()
    {

        $data = [
            'content' => '測試',
        ];

        return Mail::send('emails.test', $data, function ($message) use ($data) {
            $message->to('renfu.her@gmail.com')->subject('測試');
        });
         
    }


    public function runValidate($method)
    {
        switch ($method) {
        }

        // $this->response = self::validate($data, $rules, $this->changeErrorName);

        return $this;
    }

    public function getResponse(): object
    {
        return $this->response;
    }
    public function setResponse($response): self
    {
        $this->response = $response;
        return $this;
    }
}
