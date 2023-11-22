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

class CaseClientService extends Service
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

            $startDate = Carbon::parse($data['startDate'])->format('Y-m-d');
            $endDate = Carbon::parse($data['endDate'])->format('Y-m-d');

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                $saveData = [
                    'user_id' => $userToken->user_id,
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'mobile' => $data['mobile'],
                    'pay' => $data['pay'],
                    'status' => $data['status']
                ];

                $caseClient = CaseClient::create($saveData);

                $this->response = Service::response('success', 'OK', $caseClient->toArray());
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
                $userClient = CaseClient::where('user_id', $userToken->user_id)->where('status', 1)->get();
                foreach ($userClient as $key => $value) {
                    $userJoin = CaseJoin::where('case_client_id', $value->id)->orderByDesc('status')->first();
                    if (empty($userJoin)) {
                        $userClientArray[$vk] = $value;
                        $userClientArray[$vk]['status'] = '0';
                        // $status = (string)$userJoin->status;
                        // $userClientArray[$vk]['status'] = $status;
                        $vk++;
                    } else {
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

        $userClientArray = [];
        $vk = 0;
        $userClient = CaseClient::where('status', 1)->get();

        foreach ($userClient as $key => $value) {
            $userJoin = CaseJoin::where('case_client_id', $value->id)->where('status', '>', 0)->orderByDesc('status')->first();
            if (empty($userJoin)) {
                // dd($userJoin->status);
                $userClientArray[$vk] = $value;
                $vk++;
            }
        }

        $this->response = Service::response('success', 'OK', $userClientArray);
        return $this;
    }


    public function setStatus()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        $caseJoin = CaseJoin::where('case_client_id', (int)$data['itemId'])->update(['status' => (int)$data['status']]);

        $this->response = Service::response('success', 'OK', $caseJoin->toArray());
        return $this;
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'userToken' => 'required|string',
                    'title' => 'required|string',
                    'content' => 'required|string',
                    'startDate' => 'required|date',
                    'endDate' => 'required|date',
                    'mobile' => 'required|string',
                    'pay' => 'required|integer',
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
                ];
                $data = $this->request->toArray();
                break;
            case 'setStatus':
                $rules = [
                    'userToken' => 'required|string',
                    'itemId' => 'required|string',
                    'status' => 'required|string',
                ];
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
