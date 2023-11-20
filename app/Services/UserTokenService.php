<?php

namespace App\Services;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Models\UserToken;

class UserTokenService extends Service
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

    public function store()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        $userToken = UserToken::where('user_id', $data['user_id'])->delete();

        UserToken::create($data);
        $this->response = Service::response('success', 'ok');
        return $this;
    }


    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'user_id' => 'required|string',
                    'user_token' => 'required|string',
                    'name' => 'required|string',
                    'student_id' => 'required|string',
                    'bocoin' => 'required|integer',
                ];
                $data = $this->request->toArray();
                break;
        }

        $this->response = self::validate($data, $rules, $this->changeErrorName);

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
