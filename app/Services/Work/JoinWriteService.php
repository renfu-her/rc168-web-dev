<?php

namespace App\Services\Work;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Models\CaseClient;
use App\Models\CaseJoin;
use App\Models\UserToken;

class JoinWriteService extends Service
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

        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                $saveData = [
                    'user_id' => $userToken->user_id,
                    'user_join_id' => $data['user_join_id'],
                    'case_client_id' => $data['case_client_id'],
                    'payment' => $data['payment'],
                    'status' => $data['status']
                ];

                $caseClient = CaseJoin::create($saveData);

                $this->response = Service::response('00', 'success', $caseClient->toArray());
                return $this;
            }
        }

        $this->response = Service::response('01', 'error');
        return $this;
    }

    public function view()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();


        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {
                $userClient = CaseClient::where('user_id', $userToken->user_id)->where('status', 1)->get();

                $this->response = Service::response('00', 'success', $userClient->toArray());
                return $this;
            }
        }

        $this->response = Service::response('01', 'error');
        return $this;
    }

    public function getAll()
    {
        if (!empty($this->response)) return $this;

        $userClient = CaseClient::where('status', 1)->get();

        $this->response = Service::response('00', 'success', $userClient->toArray());
        return $this;
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'userToken' => 'required|string',
                    'case_client_id' => 'required|integer',
                    'user_join_id' => 'required|string',
                    'user_id' => 'required|string',
                    'payment' => 'required|integer',
                    'status' => 'required|integer'
                ];
                $data = $this->request->toArray();
                break;
            case 'view':
                $rules = [
                    'userToken' => 'required|string',
                ];
                $data = $this->request->toArray();
                break;
                // case 'destroy':
                //     $rules = [
                //         'id' => 'required|exists:kkdays_airport_type_codes,id',
                //     ];
                //     $data = ['id' => $this->dataId];
                //     break;
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
