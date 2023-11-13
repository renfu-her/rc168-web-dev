<?php

namespace App\Services;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Models\CaseClient;
use App\Models\CaseJoin;
use App\Models\UserToken;

class CaseJoinService extends Service
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
                $userClient = CaseJoin::where('user_id', $userToken->user_id)->get();
                foreach($userClient as $key => $value){
                    $userClient[$key]['case_client'] = CaseClient::where('id', $value['case_client_id'])->first();
                }

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
