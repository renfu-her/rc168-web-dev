<?php

namespace App\Services\Bonus;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Models\CaseClient;
use App\Models\CaseJoin;
use App\Models\UserToken;
use App\Models\UserBonus;

class UserBonusService extends Service
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

    public function save()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                $saveData = [
                    'user_id' => $userToken->user_id,
                    'bonus_id' => $data['bonus_id'],
                    'coins' => 10,
                ];

                $userBonus = UserBonus::create($saveData);

                $this->response = Service::response('success', 'OK', $userBonus->toArray());
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

        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                $userBonus = UserBonus::where('user_id', $userToken->user_id)->get();

                $this->response = Service::response('success', 'OK', $userBonus);
            }

            $this->response = Service::response('success', 'OK', []);
            return $this;
        }
    }


    public function runValidate($method)
    {
        switch ($method) {
            case 'save':
                $rules = [
                    'userToken' => 'required|string',
                    'user_id' => 'required|integer',
                    'bonus_id' => 'required|integer',
                    'coins' => 'required|integer',
                ];
                $data = $this->request->toArray();
                break;
            case 'getAll':
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
