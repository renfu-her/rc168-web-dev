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
use App\Models\UserBonus;
use App\Models\Bonus;

class BonusService extends Service
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

    public function list()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                $bonus = Bonus::where('id', $data['bonus_id'])->first();

                $this->response = Service::response('success', 'OK', $bonus);
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
        return $this;
    }


    public function store()
    {
        if (!empty($this->response)) return $this;

        $data = $this->request->toArray();

        if (!empty($data['userToken'])) {

            $userToken = UserToken::where('user_token', $data['userToken'])->first();
            if (!empty($userToken)) {

                $bonus = Bonus::where('id', $data['bonus_id'])->first();

                $data = [
                    'user_id' => $userToken->user_id,
                    'bonus_id' => $bonus->id,
                    'coins' => 10
                ];

                $userBonus = UserBonus::create($data);

                $this->response = Service::response('success', 'OK', $userBonus);
                return $this;
            }
        }

        $this->response = Service::response('error', 'userToken has error');
        return $this;
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'index':
                $rules = [
                    'userToken' => 'required|string',
                    'bonus_id' => 'required|integer',
                ];
                $data = $this->request->toArray();
                break;
            case 'store':
                $rules = [
                    'userToken' => 'required|string',
                    'bonus_id' => 'required|integer',
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
