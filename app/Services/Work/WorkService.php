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
                $userClient = CaseClient::where('id', $data['itemId'])
                    ->where('status', 1)->first();

                $status = 0;
                $userJoin = CaseJoin::where('case_client_id', $data['itemId'])->where('status', 1)->first();
                if (!empty($userJoin)) {
                    $status = 1;
                }

                $data = [
                    'case_id' => $userClient->id,
                    'user_id' => $userToken->user_id,
                    'title' => $userClient->title,
                    'content' => $userClient->content,
                    'start_date' => $userClient->start_date,
                    'end_date' => $userClient->end_date,
                    'status' => (string)$status,
                    'created_at' => $userClient->created_at,
                    'updated_at' => $userClient->updated_at,
                    'name' => $userToken->name,
                    'bocoin' => $userToken->bocoin,
                    'student_id' => $userToken->student_id,
                    'expires' => $userToken->expires,
                    'mobile' => $userClient->mobile,
                    'pay' => $userClient->pay
                ];


                $this->response = Service::response('success', 'OK', $data);
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
        return $this;
    }

    // join case status = 1, 2
    public function doCase()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                CaseJoin::where('case_client_id', $data['itemId'])->update(['status' => $data['status']]);

                $this->response = Service::response('success', 'OK', []);
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
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
            case 'view':
                $rules = [
                    'userToken' => 'required|string',
                ];
                $data = $this->request->toArray();
                break;
            case 'doCase':
                $rules = [
                    'userToken' => 'required|string',
                    'itemId' => 'required|string',
                    'status' => 'required|string',
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
