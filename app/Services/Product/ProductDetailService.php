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

        $addressId = $data['address_id'];
        $customerId = $data['customer'][0]['customer_id'];

        $address = Http::get($this->api_url . '/gws_customer_address&customer_id=' . $customerId . '&address_id=' . $addressId . '&api_key=' . $this->api_key);
        $addressData = $address->json()['customer_address'];

        $customer = Http::get($this->api_url . '/gws_customer&customer_id=' . $customerId . '&api_key=' . $this->api_key);
        $customerData = $customer->json()['customer'];

        $countryId = $customerData[0]['country_id'];
        $zoneId = $customerData[0]['zone_id'];

        // dd($data['customer'][0]['customer_id']);

        $submitData = [
            'customer[customer_id]' => $customerId,
            'customer[customer_group_id]' => 1,
            'customer[firstname]' => $customerData[0]['firstname'],
            'customer[lastname]' => $customerData[0]['lastname'],
            'customer[email]' => $customerData[0]['email'],
            'customer[telephone]' => $customerData[0]['telephone'],
            'customer[custom_field]' => '',
            'customer[fax]' => $customerData[0]['fax'],
            'payment_address[firstname]' => $customerData[0]['firstname'],
            'payment_address[lastname]' => $customerData[0]['lastname'],
            'payment_address[company]' => '',
            'payment_address[address_1]' => $customerData[0]['address_1'],
            'payment_address[address_2]' => $customerData[0]['address_2'],
            'payment_address[city]' => $customerData[0]['city'],
            'payment_address[postcode]' => $customerData[0]['postcode'],
            'payment_address[country_id]' => $countryId,
            'payment_address[zone_id]' => $customerData[0]['zone_id']

            // "address_id": "94",
            // "customer_id": "180",
            // "firstname": "her",
            // "lastname": "patrick",
            // "company": "",
            // "address_1": "address",
            // "address_2": "",
            // "city": "test city",
            // "postcode": "30000",
            // "country_id": "223",
            // "zone_id": "3625",

        ];


        // country name
        $country = Http::get($this->api_url . '/gws_country&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $countryData = $country->json()['country'];
        array_push($submitData, ["payment_address['country']" => $countryData[0]['name']]);

        $zone = Http::get($this->api_url . '/gws_zone&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $zoneData = $zone->json()['zones'];
        foreach ($zoneData as $value) {
            if ($value['zone_id'] == $zoneId) {
                array_push($submitData, ["payment_address['zone']" => $value['name']]);
            }
        }



        // zone name

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
