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

                $this->response = Service::response('success', 'success', $caseClient->toArray());
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
        return $this;
    }

    public function view()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();
        $vk = 0;
        $userClientArray = [];
        if (!empty($data['userToken'])) {
            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {
                $userClient = CaseClient::where('status', 1)->get();
                foreach ($userClient as $key => $value) {
                    $userJoin = CaseJoin::where('case_client_id', $value->id)->where('user_id', $userToken->user_id)->first();
                    if (!empty($userJoin)) {
                        $userClientArray[$vk] = $value;
                        $status = (string)$userJoin->status;
                        $userClientArray[$vk]['status'] = $status;
                        $vk++;
                    }
                }

                $this->response = Service::response('success', 'OK', $userClientArray);
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
        return $this;
    }

    public function getAll()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        $vk = 0;
        $userClientArray = [];
        if (!empty($data['userToken'])) {
            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {
                $userJoin = CaseJoin::where('case_client_id', $data['itemId'])->where('status', '<', 1)->get();
                foreach ($userJoin as $joinKey => $joinValue) {
                    $caseClient = CaseClient::where('id', $joinValue->id)->first();
                    if (!empty($caseClient)) {
                        $userClientArray[$vk] = $caseClient;
                        $status = (string)$joinValue->status;
                        $case_client_id = (string)$joinValue->case_client_id;

                        $userClientArray[$vk]['status'] = $status;
                        $userClientArray[$vk]['case_client_id'] = $case_client_id;
                        $userClientArray[$vk]['user_join_id'] = $joinValue->user_join_id;
                        $userClientArray[$vk]['name'] = $userToken->name;
                        $vk++;
                    }
                }

                $this->response = Service::response('success', 'OK', $userClientArray);
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
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
            case 'getAll':
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
