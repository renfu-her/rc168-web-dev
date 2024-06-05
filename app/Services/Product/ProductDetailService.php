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
            return Service::response('error', 'Order data not found');
        }

        $data = json_decode($orderData->data, true);

        $this->logOrderEvent('訂單建立', $data);

        $vipCustomer = str_replace('$', '', $data['totals'][1]['text']);

        $addressData = $this->getCustomerAddress($data['customer'][0]['customer_id'], $data['address_id']);
        $customerData = $this->getCustomerData($data['customer'][0]['customer_id']);

        $submitData = $this->prepareSubmitData($data, $addressData, $customerData);

        $this->logOrderEvent('訂單 submitData', $submitData);

        $result = Http::asForm()->post(
            $this->api_url .
                '/gws_appcustomer_order/add&customer_id=' . $data['customer'][0]['customer_id'] .
                '&api_key=' . $this->api_key,
            $submitData,
        );

        $this->response = Service::response('success', 'OK', $result->json());

        return $this;
    }

    private function logOrderEvent($event, $data)
    {
        $orderLog = new OrderLog();
        $orderLog->events = $event;
        $orderLog->logs = json_encode($data);
        $orderLog->save();
    }

    private function getCustomerAddress($customerId, $addressId)
    {
        $response = Http::get($this->api_url .
            '/gws_customer_address&customer_id=' . $customerId .
            '&address_id=' . $addressId .
            '&api_key=' . $this->api_key);

        return $response->json()['customer_address'][0];
    }

    private function getCustomerData($customerId)
    {
        $response = Http::get($this->api_url .
            '/gws_customer&customer_id=' . $customerId .
            '&api_key=' . $this->api_key);

        return $response->json()['customer'][0];
    }

    private function getCountryAndZone($countryId, $zoneId)
    {
        $countryResponse = Http::get(
            $this->api_url .
                '/gws_country&country_id=' . $countryId .
                '&api_key=' . $this->api_key
        );

        $zoneResponse = Http::get($this->api_url .
            '/gws_zone&country_id=' . $countryId .
            '&api_key=' . $this->api_key);

        $countryName = $countryResponse->json()['country'][0]['name'];
        $zoneData = collect($zoneResponse->json()['zones'])->firstWhere('zone_id', $zoneId);

        return [
            'country' => $countryName,
            'zone' => $zoneData['name']
        ];
    }

    private function prepareSubmitData($data, $addressData, $customerData)
    {
        $customerId = $data['customer'][0]['customer_id'];
        $countryAndZone = $this->getCountryAndZone($addressData['country_id'], $addressData['zone_id']);

        $submitData = [
            'customer' => [
                'customer_id' => $customerId,
                'customer_group_id' => 1,
                'firstname' => $customerData['firstname'],
                'lastname' => $customerData['lastname'],
                'email' => $customerData['email'],
                'telephone' => $customerData['telephone'],
                'custom_field' => '',
                'fax' => $customerData['fax']
            ],
            'payment_address' => $this->prepareAddressData($addressData, $customerData, $countryAndZone),
            'shipping_address' => $this->prepareAddressData($addressData, $customerData, $countryAndZone),
            'payment_method' => $this->preparePaymentMethod($data['payment_method']),
            'products' => $this->prepareProducts($data['products']),
            'totals' => $this->prepareTotals($data['totals'], $data['shipping_cost'], $data['payment_method']),
            'total' => array_sum(array_column($data['products'], 'total')) + $data['shipping_cost'],
            'shipping_method' => [
                'title' => '運費',
                'code' => 'flat.flat'
            ]
        ];

        return $submitData;
    }

    private function prepareAddressData($addressData, $customerData, $countryAndZone)
    {
        return [
            'firstname' => $customerData['firstname'],
            'lastname' => $customerData['lastname'],
            'company' => '',
            'address_1' => $addressData['address_1'],
            'address_2' => $addressData['address_2'],
            'city' => $addressData['city'],
            'postcode' => $addressData['postcode'],
            'country_id' => $addressData['country_id'],
            'zone_id' => $addressData['zone_id'],
            'country' => $countryAndZone['country'],
            'zone' => $countryAndZone['zone'],
            'custom_field' => ['1' => '711'],
            'address_format' => "{$addressData['postcode']} {$countryAndZone['country']} {$countryAndZone['zone']} {$addressData['address_1']}",
            'cellphone' => '0922013171',
            'pickupstore' => '0922013171'
        ];
    }

    private function preparePaymentMethod($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'linepay_sainent':
                return ['title' => 'LINE Pay', 'code' => 'linepay_sainent'];
            case 'ecpaypayment':
                return ['title' => '線上刷卡', 'code' => 'ecpaypayment'];
            default:
                return ['title' => '銀行轉帳', 'code' => 'bank_transfer'];
        }
    }

    private function prepareProducts($products)
    {
        return collect($products)->map(function ($product, $key) {
            return [
                'product_id' => $product['product_id'],
                'model' => $product['name'],
                'name' => $product['name'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'total' => $product['total'],
                'tax_class_id' => 9,
                'download' => '',
                'subtract' => 1,
                'reward' => 0,
                'option' => $this->prepareProductOptions($product['options'] ?? [])
            ];
        })->toArray();
    }

    private function prepareProductOptions($options)
    {
        return collect($options)->mapWithKeys(function ($option, $key) {
            return [
                $key => [
                    'product_option_id' => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'name' => $option['name'],
                    'value' => $option['value'],
                    'type' => $option['type']
                ]
            ];
        })->toArray();
    }

    private function prepareTotals($totals, $shippingCost, $paymentMethod)
    {
        $additionalTotals = collect([
            [
                'code' => $paymentMethod,
                'title' => '運費',
                'value' => $shippingCost,
                'sort_order' => 1
            ]
        ]);

        $mappedTotals = collect($totals)->mapWithKeys(function ($total, $key) use ($shippingCost) {
            $value = str_replace('$', '', $total['text']);
            if ($total['code'] === 'total') {
                $value += $shippingCost;
            }
            // if ($total['code'] === 'vip_customer') {
            //     $value = '$' . $vipCustomer;
            // }

            return [
                $key => [
                    'code' => $total['code'],
                    'title' => $total['title'],
                    'value' => $value,
                    'sort_order' => $key + 2 // 確保排序從2開始，1是運費
                ]
            ];
        });

        return $additionalTotals->merge($mappedTotals)->sortBy('sort_order')->values()->toArray();
    }

    private function getPaymentMethodTitle($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'linepay_sainent':
                return "LINE Pay";
            case 'ecpaypayment':
                return "線上刷卡";
            default:
                return "銀行轉帳";
        }
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
