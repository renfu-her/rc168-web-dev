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
                $userClient = CaseClient::where('user_id', $userToken->user_id)
                    ->where('id', $data['itemId'])
                    ->where('status', 1)->first();

                $data = [
                    'case_id' => $userClient->id,
                    'user_id' => $userToken->user_id,
                    'title' => $userClient->title,
                    'content' => $userClient->content,
                    'start_date' => $userClient->start_date,
                    'end_date' => $userClient->end_date,
                    'status' => $userClient->status,
                    'created_at' => $userClient->created_at,
                    'updated_at' => $userClient->updated_at,
                    'name' => $userToken->name,
                    'bocoin' => $userToken->bocoin,
                    'student_id' => $userToken->student_id,
                    'expires' => $userToken->expires,
                    'mobile' => $userClient->mobile
                ];


                $this->response = Service::response('00', 'success', $data);
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
        $vk = 0;
        $userClientArray = [];
        if (!empty($data['userToken'])) {
            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {
                $userClient = CaseClient::where('user_id', $userToken->user_id)->where('status', 1)->get();
                foreach($userClient as $key => $value){
                    $userJoin = CaseJoin::where('case_client_id', $value->id)->where('status', 1)->first();
                    if($userJoin){
                        $userClientArray[$vk] = $value;
                        $vk++;
                    }
                }

                $this->response = Service::response('00', 'success', $userClientArray);
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
            case 'view':
                $rules = [
                    'userToken' => 'required|string',
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
