<?php

namespace App\Services;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use Yaza\LaravelGoogleDriveStorage\Gdrive;
use Illuminate\Support\Facades\Storage;
use Google\Client;


class GoogleService extends Service
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
        $client = new Client();
        $client->setAuthConfig(config('config.GOOGLE_KEY'));
        $client->addScope(\Google\Service\Drive::DRIVE);
        $client->setIncludeGrantedScopes(true);
        $client->setAccessType('online');
        $service = new \Google\Service\Drive($client);

        $folderId = '1BHt8CrJLUM_EEwyeXO7PoCsbITpgT1CH';

        $optParams = array(
            'pageSize' => 100,
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives' => true,
            'fields' => 'nextPageToken, files(id, name)',
            'q' => "'" . $folderId . "' in parents and trashed=false"
        );
        $response = $service->files->listFiles($optParams);

        return $response->getFiles();
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [];
                $data = $this->request->toArray();
                break;
            case 'view':
                $rules = [];
                $data = $this->request->toArray();
                break;
            case 'getAll':
                $rules = [];
                $data = $this->request->toArray();
                break;
            case 'setStatus':
                $rules = [];
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
