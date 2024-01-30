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

        $countryId = $addressData[0]['country_id'];
        $zoneId = $addressData[0]['zone_id'];

        // dd($data['customer'][0]['customer_id']);

        $submitData = [
            'customer' => [
                'customer_id' => $customerId,
                'customer_group_id' => 1,
                'firstname' => $customerData[0]['firstname'],

            ],
            'customer[customer_group_id]' => 1,
            'customer[firstname]' => $customerData[0]['firstname'],
            'customer[lastname]' => $customerData[0]['lastname'],
            'customer[email]' => $customerData[0]['email'],
            'customer[telephone]' => $customerData[0]['telephone'],
            'customer[custom_field]' => '',
            'customer[fax]' => $customerData[0]['fax'],

            // payment_address
            'payment_address[firstname]' => $customerData[0]['firstname'],
            'payment_address[lastname]' => $customerData[0]['lastname'],
            'payment_address[company]' => '',
            'payment_address[address_1]' => $addressData[0]['address_1'],
            'payment_address[address_2]' => $addressData[0]['address_2'],
            'payment_address[city]' => $addressData[0]['city'],
            'payment_address[postcode]' => $addressData[0]['postcode'],
            'payment_address[country_id]' => $countryId,
            'payment_address[zone_id]' => $zoneId,
            'payment_method[title]' => "運費",
            'payment_method[code]' => "flat.flat",

            // shipping_address
            'shipping_address[firstname]' => $customerData[0]['firstname'],
            'shipping_address[lastname]' => $customerData[0]['lastname'],
            'shippig_address[company]' => '',
            'shippig_address[address_1]' => $addressData[0]['address_1'],
            'shippig_address[address_2]' => $addressData[0]['address_2'],
            'shippig_address[city]' => $addressData[0]['city'],
            'shippig_address[postcode]' => $addressData[0]['postcode'],
            'shippig_address[country_id]' => $countryId,
            'shippig_address[zone_id]' => $zoneId
        ];


        // country name
        $country = Http::get($this->api_url . '/gws_country&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $countryData = $country->json()['country'];
        $submitData["payment_address['country']"] = $countryData[0]['name'];
        $submitData["shippig_address['country']"] = $countryData[0]['name'];

        $zone = Http::get($this->api_url . '/gws_zone&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $zoneData = $zone->json()['zones'];
        foreach ($zoneData as $value) {
            if ($value['zone_id'] == $zoneId) {
                $submitData["payment_address['zone']"] = $value['name'];
                $submitData["shippig_address['zone']"] = $value['name'];
            }
        }

        // product array
        $total = 0;
        foreach ($data['products'] as $key => $value) {
            $submitData["products[" . $key . "]['product_id']"] = $value['product_id'];
            $submitData["products[" . $key . "]['model']"] = $value['product_id'];
            $submitData["products[" . $key . "]['name']"] = $value['name'];
            $submitData["products[" . $key . "]['quantity']"] = $value['quantity'];
            $submitData["products[" . $key . "]['price']"] = $value['price'];
            $submitData["products[" . $key . "]['total']"] = $value['total'];
            $submitData["products[" . $key . "]['tax_class_id']"] = 9;

            $submitData["products[" . $key . "]['download']"] = '';
            $submitData["products[" . $key . "]['subtract']"] = 1;
            $submitData["products[" . $key . "]['reward']"] = 0;
            $total += $value['total'];
        }

        $submitData['total'] = $total;
        $submitData["totals[0]['code']"] = "sub_total";
        $submitData["totals[0]['title']"] = "Sub-Total";
        $submitData["totals[0]['value']"] = $total;
        $submitData["totals[0]['sort_order']"] = "1";

        // shipping_method
        $submitData["shipping_method['title']"] = "LINE Pay";
        $submitData["shipping_method['code']"] = "linepay_sainent";


        $result = Http::asForm()
            ->post($this->api_url . '/gws_customer_order/add&country_id=' . $countryId . '&api_key=' . $this->api_key, $submitData);
        dd($result->json(), $submitData);

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
