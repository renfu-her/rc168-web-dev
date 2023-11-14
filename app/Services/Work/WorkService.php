<?php

namespace App\Services\Work;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Models\CaseClient;
use App\Models\UserToken;

class WorkService extends Service
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


    public function viewDetail()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();


        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {
                $userClient = CaseClient::where('user_id', $userToken->user_id)
                    ->where('id', $data['itemId'])
                    ->where('status', 1)->first();

                $this->response = Service::response('00', 'success', $userClient->toArray());
                return $this;
            }
        }

        $this->response = Service::response('01', 'error');
        return $this;
    }

    public function runValidate($method)
    {
        switch ($method) {

            case 'viewDetail':
                $rules = [
                    'userToken' => 'required|string',
                    'itemId' => 'required|integer',
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
