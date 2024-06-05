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

        $data = json_decode($orderData->data, true);

        $orderLog = new OrderLog();
        $orderLog->events = '訂單建立';
        $orderLog->logs = json_encode($data);
        $orderLog->save();

        Storage::disk('public')->put('data-' . $customerId, json_encode($data));

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
            'customer[customer_id]' => $customerId,
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


        // country name
        $country = Http::get($this->api_url . '/gws_country&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $countryData = $country->json()['country'];
        $submitData["payment_address[country]"] = $countryData[0]['name'];
        $submitData["shipping_address[country]"] = $countryData[0]['name'];

        $zone = Http::get($this->api_url . '/gws_zone&country_id=' . $countryId . '&api_key=' . $this->api_key);
        $zoneData = $zone->json()['zones'];
        foreach ($zoneData as $value) {
            if ($value['zone_id'] == $zoneId) {
                $submitData["payment_address[zone]"] = $value['name'];
                $submitData["shipping_address[zone]"] = $value['name'];
            }
        }

        $submitData['payment_address[address_format]'] = $addressData[0]['postcode'] . ' ' . $countryData[0]['name'] . $submitData["payment_address[zone]"] . $addressData[0]['address_1'];
        $submitData['shipping_address[address_format]'] = $addressData[0]['postcode'] . ' ' . $countryData[0]['name'] . $submitData["payment_address[zone]"] . $addressData[0]['address_1'];

        // payment
        $payment_method = $data['payment_method'];
        if ($payment_method == 'linepay_sainent') {
            $submitData['payment_method[title]'] = "LINE Pay";
            $submitData['payment_method[code]'] = "linepay_sainent";
        } elseif ($payment_method == 'ecpaypayment') {
            $submitData['payment_method[title]'] = "線上刷卡";
            $submitData['payment_method[code]'] = "ecpaypayment";
        } else {
            $submitData['payment_method[title]'] = "銀行轉帳";
            $submitData['payment_method[code]'] = "bank_transfer";
        }

        // product array
        $total = 0;
        foreach ($data['products'] as $key => $value) {
            $submitData["products[" . $key . "][product_id]"] = $value['product_id'];
            $submitData["products[" . $key . "][model]"] = $value['name'];
            $submitData["products[" . $key . "][name]"] = $value['name'];
            $submitData["products[" . $key . "][quantity]"] = $value['quantity'];
            $submitData["products[" . $key . "][price]"] = $value['price'];
            $submitData["products[" . $key . "][total]"] = $value['total'];
            $submitData["products[" . $key . "][tax_class_id]"] = 9;
            $submitData["products[" . $key . "][download]"] = '';
            $submitData["products[" . $key . "][subtract]"] = 1;
            $submitData["products[" . $key . "][reward]"] = 0;

            // 處理選項
            if (isset($value['options']) && is_array($value['options'])) {
                foreach ($value['options'] as $optionKey => $optionValue) {
                    $submitData["products[" . $key . "][option][" . $optionKey . "][product_option_id]"] = $optionValue['product_option_id'];
                    $submitData["products[" . $key . "][option][" . $optionKey . "][product_option_value_id]"] = $optionValue['product_option_value_id'];
                    $submitData["products[" . $key . "][option][" . $optionKey . "][name]"] = $optionValue['name'];
                    $submitData["products[" . $key . "][option][" . $optionKey . "][value]"] = $optionValue['value'];
                    $submitData["products[" . $key . "][option][" . $optionKey . "][type]"] = $optionValue['type'];
                }
            }

            $total += $value['total'];
        }

        foreach ($data['totals'] as $key => $totals) {
            $submitData["totals[$key][code]"] = $totals['code'];
            $submitData["totals[$key][title]"] = $totals['title'];
            $submitData["totals[$key][value]"] = substr_replace('$', '', $totals['text']);
            $submitData["totals[$key][sort_order]"] = $key + 1;
        }

        $submitData['total'] = $total;
        // $submitData["totals[0][code]"] = "sub_total";
        // $submitData["totals[0][title]"] = "Sub-Total";
        // $submitData["totals[0][value]"] = $total;
        // $submitData["totals[0][sort_order]"] = "1";

        // shipping_method
        $submitData["shipping_method[title]"] = "運費";
        $submitData["shipping_method[code]"] = "flat.flat";
        $submitData["payment_address[cellphone]"] = "0922013171";
        $submitData["payment_address[pickupstore]"] = "0922013171";
        $submitData["shipping_address[cellphone]"] = "0922013171";
        $submitData["shipping_address[pickupstore]"] = "0922013171";


        $orderLog = new OrderLog();
        $orderLog->events = '訂單 submitData';
        $orderLog->logs = json_encode($submitData);
        $orderLog->save();
        // Storage::disk('public')->put($customerId . ".txt", json_encode($data));
        // Storage::disk('public')->put('customerId', json_encode($data));
        // Storage::disk('public')->put('submitData', json_encode($submitData));

        $result = Http::asForm()
            ->post($this->api_url . '/gws_appcustomer_order/add&customer_id=' . $customerId . '&api_key=' . $this->api_key, $submitData);

        // dd($result->body(), $result->json());

        $this->response = Service::response('success', 'OK', $result->json());

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
