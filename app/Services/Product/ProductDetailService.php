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
                'lastname' => $customerData[0]['lastname'],
                'email' => $customerData[0]['email'],
                'telephone' => $customerData[0]['telephone'],
                'custom_field' => '',
                'fax' => $customerData[0]['fax'],
            ],
            'payment' => [
                'firstname' => $addressData[0]['firstname'],
                'lastname' => $addressData[0]['lastname'],
                'country' => '',
                'address_1' => $addressData[0]['address_1'],
                'address_2' => $addressData[0]['address_2'],
                'city' => $addressData[0]['city'],
                'postcode' => $addressData[0]['postcode'],
                'country_id' => $addressData[0]['country_id'],
                'zone_id' => $addressData[0]['zone_id'],
                'address_form' => $addressData[0]['address_1']
            ],
            'payment_method' => [
                'title' => '運費',
                'code' => 'flat.flat'
            ],
            'shipping_address' => [
                'address_form' => $addressData[0]['address_1'],
                'firstname' => $customerData[0]['firstname'],
                'lastnamme' => $customerData[0]['lastname'],
                'company' => '',
                'city' => $addressData[0]['city'],
                'postcode' => $addressData[0]['postcode'],
                'country_id' => $addressData[0]['country_id'],
                'zone_id' => $addressData[0]['zone_id'],
            ],

        ];


        // country name
        $country = Http::get($this->api_url . '/gws_country&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $countryData = $country->json()['country'];
        $submitData["payment_address[country]"] = $countryData[0]['name'];
        $submitData["shippig_address[country]"] = $countryData[0]['name'];

        $zone = Http::get($this->api_url . '/gws_zone&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $zoneData = $zone->json()['zones'];
        foreach ($zoneData as $value) {
            if ($value['zone_id'] == $zoneId) {
                $submitData["payment_address[zone]"] = $value['name'];
                $submitData["shippig_address[zone]"] = $value['name'];
            }
        }

        // product array
        $total = 0;
        foreach ($data['products'] as $key => $value) {
            $submitData["products"] = [
                'product_id' => $value['product_id'],
                'model' => $value['product_id'],
                'name' => $value['name'],
                'quantity' => $value['quantity'],
                'price' => $value['price'],
                'total' => $value['total'],
                'tax_class_id' => 9,
                'download' => '',
                'subtract' => 1,
                'reward' => 0
            ];
            $total += $value['total'];
        }

        $submitData['total'] = $total;
        $submitData['totals'] = [
            'code' => 'sub_total',
            'title' => 'Sub-Total',
            'value' => $total,
            'sort_order' => 1
        ];

        $submitData["shipping_method"] = [
            'title' => 'LINE Pay',
            'code' =>   'linepay_sainent'
        ];


        $result = Http::asForm()
            ->post($this->api_url . '/gws_customer_order/add&customer_id=' . $customerId . '&api_key=' . $this->api_key, $submitData);
        dd($result->body(), $submitData);

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
