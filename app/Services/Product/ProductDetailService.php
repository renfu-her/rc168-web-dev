<?php

namespace App\Services\Product;

use App\Services\Service;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

use App\Models\Order;
use App\Models\OrderData;
use App\Models\OrderLog;

use TsaiYiHua\ECPay\Checkout;
use Symfony\Component\DomCrawler\Crawler;
use voku\helper\HtmlDomParser;
use Illuminate\Support\Facades\Storage;

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

    public function setOrder($customerId)
    {
        $orderData = OrderData::where('customer_id', $customerId)->first();
        if (!$orderData) {
            return $this->response = Service::response('error', 'Order data not found.');
        }

        $data = json_decode($orderData->data, true);

        $this->logOrderEvent('訂單建立', $data);

        $addressId = $data['address_id'];
        $customerId = $data['customer'][0]['customer_id'];

        $addressData = $this->fetchData('gws_customer_address', [
            'customer_id' => $customerId,
            'address_id' => $addressId
        ])['customer_address'];

        $customerData = $this->fetchData('gws_customer', [
            'customer_id' => $customerId
        ])['customer'];

        $countryId = $addressData[0]['country_id'];
        $zoneId = $addressData[0]['zone_id'];

        $submitData = $this->buildSubmitData($data, $customerData, $addressData, $countryId, $zoneId);
        $this->addCountryAndZoneNames($submitData, $countryId, $zoneId);

        ksort($submitData);

        $this->logOrderEvent('訂單 submitData', $submitData);

        $result = Http::asForm()->post($this->api_url . '/gws_appcustomer_order/add&customer_id=' . $customerId . '&api_key=' . $this->api_key, $submitData);

        if ($result->failed()) {
            return $this->response = Service::response('error', 'Order submission failed.', $result->json());
        }

        return $this->response = Service::response('success', 'OK', $result->json());
    }

    private function logOrderEvent($event, $data)
    {
        $orderLog = new OrderLog();
        $orderLog->events = $event;
        $orderLog->logs = json_encode($data);
        $orderLog->save();
    }

    private function fetchData($endpoint, $params)
    {
        $response = Http::get($this->api_url . "/$endpoint", array_merge($params, ['api_key' => $this->api_key]));
        if ($response->failed()) {
            throw new Exception("Failed to fetch data from $endpoint");
        }
        return $response->json();
    }

    private function buildSubmitData($data, $customerData, $addressData, $countryId, $zoneId)
    {
        $submitData = [
            'customer[customer_id]' => $customerData[0]['customer_id'],
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
            'payment_address[custom_field][1]' => '711',

            // shipping_address
            'shipping_address[firstname]' => $customerData[0]['firstname'],
            'shipping_address[lastname]' => $customerData[0]['lastname'],
            'shipping_address[company]' => '',
            'shipping_address[address_1]' => $addressData[0]['address_1'],
            'shipping_address[address_2]' => $addressData[0]['address_2'],
            'shipping_address[city]' => $addressData[0]['city'],
            'shipping_address[postcode]' => $addressData[0]['postcode'],
            'shipping_address[country_id]' => $countryId,
            'shipping_address[zone_id]' => $zoneId,
            'shipping_address[address_format]' => $addressData[0]['address_1'],
            'shipping_address[custom_field][1]' => '711',
        ];

        $this->addProductsToSubmitData($submitData, $data['products']);
        $this->addTotalsToSubmitData($submitData, $data['totals']);

        $submitData['total'] = array_reduce($data['products'], function ($carry, $item) {
            return $carry + $item['total'];
        }, 0);

        $this->addPaymentMethod($submitData, $data['payment_method']);

        return $submitData;
    }

    private function addProductsToSubmitData(&$submitData, $products)
    {
        foreach ($products as $key => $product) {
            $submitData["products[$key][product_id]"] = $product['product_id'];
            $submitData["products[$key][model]"] = $product['name'];
            $submitData["products[$key][name]"] = $product['name'];
            $submitData["products[$key][quantity]"] = $product['quantity'];
            $submitData["products[$key][price]"] = $product['price'];
            $submitData["products[$key][total]"] = $product['total'];
            $submitData["products[$key][tax_class_id]"] = 9;
            $submitData["products[$key][download]"] = '';
            $submitData["products[$key][subtract]"] = 1;
            $submitData["products[$key][reward]"] = 0;

            if (isset($product['options']) && is_array($product['options'])) {
                foreach ($product['options'] as $optionKey => $optionValue) {
                    $submitData["products[$key][option][$optionKey][product_option_id]"] = $optionValue['product_option_id'];
                    $submitData["products[$key][option][$optionKey][product_option_value_id]"] = $optionValue['product_option_value_id'];
                    $submitData["products[$key][option][$optionKey][name]"] = $optionValue['name'];
                    $submitData["products[$key][option][$optionKey][value]"] = $optionValue['value'];
                    $submitData["products[$key][option][$optionKey][type]"] = $optionValue['type'];
                }
            }
        }
    }

    private function addTotalsToSubmitData(&$submitData, $totals)
    {
        foreach ($totals as $key => $total) {
            $submitData["totals[$key][code]"] = $total['code'];
            $submitData["totals[$key][title]"] = $total['title'];
            $submitData["totals[$key][value]"] = str_replace('$', '', $total['text']);
            $submitData["totals[$key][sort_order]"] = $key + 1;
        }
    }

    private function addPaymentMethod(&$submitData, $paymentMethod)
    {
        $methods = [
            'linepay_sainent' => ['title' => 'LINE Pay', 'code' => 'linepay_sainent'],
            'ecpaypayment' => ['title' => '線上刷卡', 'code' => 'ecpaypayment'],
            'default' => ['title' => '銀行轉帳', 'code' => 'bank_transfer']
        ];

        $method = $methods[$paymentMethod] ?? $methods['default'];
        $submitData['payment_method[title]'] = $method['title'];
        $submitData['payment_method[code]'] = $method['code'];
    }

    private function addCountryAndZoneNames(&$submitData, $countryId, $zoneId)
    {
        $countryData = $this->fetchData('gws_country', [
            'country_id' => $countryId
        ])['country'];

        $submitData["payment_address[country]"] = $countryData[0]['name'];
        $submitData["shipping_address[country]"] = $countryData[0]['name'];

        $zoneData = $this->fetchData('gws_zone', [
            'country_id' => $countryId
        ])['zones'];

        foreach ($zoneData as $value) {
            if ($value['zone_id'] == $zoneId) {
                $submitData["payment_address[zone]"] = $value['name'];
                $submitData["shipping_address[zone]"] = $value['name'];
            }
        }

        $submitData['payment_address[address_format]'] = $this->formatAddress($submitData['payment_address'], $countryData[0]['name']);
        $submitData['shipping_address[address_format]'] = $this->formatAddress($submitData['shipping_address'], $countryData[0]['name']);
    }

    private function formatAddress($address, $countryName)
    {
        return $address['postcode'] . ' ' . $countryName . $address['zone'] . $address['address_1'];
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
