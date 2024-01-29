<?php

namespace App\Services\Product;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use Symfony\Component\DomCrawler\Crawler;
use voku\helper\HtmlDomParser;

class ProductDetailService extends Service
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

    // TODO: 產品明細
    public function detail()
    {

        $data = $this->request->toArray();

        $productDetail = Http::get($this->api_url . '/gws_product&product_id=' . $this->dataId . '&api_key=' . $this->api_key);

        $res = $productDetail->body();

        $res = json_decode($res, true);

        $prodDetail = $res['product'][0];

        $httpHref = html_entity_decode($prodDetail['href']);

        $dom = HtmlDomParser::file_get_html($httpHref);

        $images = [];
        foreach ($dom->find('.thumbnail') as $e) {
            // $imgSrc = str_replace("74x74", "500x400", $e->src);
            // if(empty($imgSrc)){
            //     $imgSrc = $e->src;
            // }
            array_push($images, $e->href);
        }

        // dd($images);

        $result = $prodDetail;
        $result['images'] = $images;

        $this->response = Service::response('success', 'OK', $result);

        return $this;
    }

    public function getContent()
    {
        $data = $this->request->toArray();

        $productDetail = Http::get($this->api_url . '/gws_product&product_id=' . $this->dataId . '&api_key=' . $this->api_key);

        $res = $productDetail->body();

        $res = json_decode($res, true);

        $description = $res['product'][0]['description'];

        $this->response = Service::response('success', 'OK', $description);

        return $this;
    }

    public function setOrder()
    {
        $data = $this->request->toArray();

        $address = Http::get($this->api_url . '/gws_customer_address&customer_id=' . $data['customer'][0]['customer_id'] . '&address_id=' . $data['address_id'] . '&api_key=' . $this->api_key);
        $addressData = $address->json()['customer_address'];

        $customer = Http::get($this->api_url . '/gws_customer&customer_id=' . $data['customer'][0]['customer_id'] . '&api_key=' . $this->api_key);
        $customerData = $customer->json()['customer'];

        // dd($data['customer'][0]['customer_id']);

        $submitData = [
            'customer[customer_id]' => $customerData[0]['customer_id'],
            'customer[customer_group_id]' => 1,
            'customer[firstname]' => $customerData[0]['firstname'],
            'customer[lastname]' => $customerData[0]['lastname'],
            'customer[email]' => $customerData[0]['email'],
            'customer[telephone]' => $customerData[0]['telephone'],
        ];


        $this->response = Service::response('success', 'OK', $submitData);

        return $this;
    }



    public function runValidate($method)
    {
        switch ($method) {
                // case 'store':
                //     $rules = [
                //         'userToken' => 'required|string',
                //         'title' => 'required|string',
                //         'content' => 'required|string',
                //         'startDate' => 'required|date',
                //         'endDate' => 'required|date',
                //         'mobile' => 'required|string',
                //         'pay' => 'required|integer',
                //         'status' => 'required|integer'
                //     ];
                //     $data = $this->request->toArray();
                //     break;
                // case 'view':
                //     $rules = [
                //         'userToken' => 'required|string',
                //     ];
                //     $data = $this->request->toArray();
                //     break;
                // case 'getAll':
                //     $rules = [
                //         'userToken' => 'required|string',
                //     ];
                //     $data = $this->request->toArray();
                //     break;
                // case 'setStatus':
                //     $rules = [
                //         'userToken' => 'required|string',
                //         'itemId' => 'required|string',
                //         'status' => 'required|string',
                //         'join_id' => 'required|string',
                //     ];
                //     $data = $this->request->toArray();
                //     break;
        }

        // $this->response = self::validate($data, $rules, $this->changeErrorName);

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
