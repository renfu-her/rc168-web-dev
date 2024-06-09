<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Models\Order;
use App\Models\OrderData;
use App\Models\OrderLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductPaymentController extends Controller
{

    protected $checkout;
    public $api_url = 'https://ismartdemo.com.tw/index.php?route=extension/module/api';
    public $api_key = 'CNQ4eX5WcbgFQVkBXFKmP9AE2AYUpU2HySz2wFhwCZ3qExG6Tep7ZCSZygwzYfsF';

    public function __construct(Checkout $checkout)
    {
        $this->checkout = $checkout;
    }
    // index
    public function payment(Request $request)
    {

        $req = $request->all();

        $content = OrderData::where('customer_id', $req['customerId'])->first()->data;

        $data = json_decode($content, true);

        switch ($data['payment_method']) {
            case 'ecpaypayment':
                $payment = $this->ecpay($data, $req);
                break;
            case 'linepay_sainent':
                $payment = $this->linepay($data, $req);
                break;
            case 'bank_transfer':
                $payment = $this->bankTransfer($req);
                break;
        }

        return $payment;
    }

    // 銀行轉帳
    public function bankTransfer($req)
    {
        $customerId = $req['customerId'];
        $apiEndpoint = $this->api_url . '/gws_apppayment_methods/index';
        $queryParams = http_build_query([
            'customer_id' => $customerId,
            'api_key' => $this->api_key,
        ]);

        $response = Http::get("{$apiEndpoint}&{$queryParams}");

        if ($response->status() === 200) {
            $bankTransferData = $response->json();
            $paymentMethods = collect($bankTransferData['payment_methods']);

            // 過濾並取得 code 為 bank_transfer 的支付方式
            $bankTransferMethod = $paymentMethods->firstWhere('code', 'bank_transfer');

            if ($bankTransferMethod) {
                // 取得 desc 值並替換換行符號
                $description = str_replace("\r\n", "<br>", $bankTransferMethod['desc']);

                // 移除購物車項目
                $this->fetchAndRemoveCartItems($req);

                // 回傳描述
                return $description;
            } else {
                return 'Bank transfer method not found';
            }
        }

        return response()->json(['error' => 'API request error'], 500);
    }

    public function fetchAndRemoveCartItems($req)
    {
        $customerId = $req['customerId'];

        try {
            // 发起GET请求以获取购物车数据
            $response = Http::get("{$this->api_url}/gws_customer_cart", [
                'customer_id' => $customerId,
                'api_key' => $this->api_key,
            ]);

            if ($response->successful()) {
                // 请求成功，解析购物车数据
                $jsonData = $response->json();
                $carts = collect($jsonData['customer_cart']);

                // 遍历购物车项并删除它们
                $carts->each(function ($cart) use ($customerId) {
                    $this->removeCartItem($customerId, $cart['cart_id']);
                });

                return true;
            } else {
                // 错误处理
                Log::error('Failed to fetch cart items: ' . $response->status());
                return false;
            }
        } catch (Exception $e) {
            // 异常处理
            Log::error('Error fetching cart items: ' . $e->getMessage());
            return false;
        }
    }

    private function removeCartItem($customerId, $cartId)
    {
        try {
            $response = Http::get("{$this->api_url}/gws_customer_cart/remove", [
                'customer_id' => $customerId,
                'cart_id' => $cartId,
                'api_key' => $this->api_key,
            ]);

            if ($response->successful()) {
                // 请求成功处理
                Log::info('Item removed successfully: ' . $response->body());
            } else {
                // 错误处理
                Log::error('Failed to remove item: ' . $response->status());
            }
        } catch (Exception $e) {
            // 异常处理
            Log::error('Error removing item: ' . $e->getMessage());
        }
    }


    // ecpay
    public function ecpay($data, $req)
    {

        // $addressId = $data['address_id'];
        $customerId = $data['customer'][0]['customer_id'];

        $items = [];
        $itemDescription = '';
        $total = 0;
        foreach ($data['products'] as $key => $value) {
            $items[$key] = [
                'name' => $value['name'],
                'qty' => $value['quantity'],
                'unit' => '個',
                'price' => $value['price'],
            ];
            $itemDescription .= $value['name'] . '|';
            $total += $value['total'];
        }

        if (!empty($data['shipping_cost'])) {
            $items[count($data['products']) + 1] = [
                'name' => '運費',
                'qty' => 1,
                'unit' => '件',
                'price' => $data['shipping_cost'],
            ];
        }

        $formData = [
            'ItemDescription' => $itemDescription,
            'Items' => $items,
            'PaymentMethod' => 'Credit',
            'UserId' => $customerId,
            'OrderResultURL' => env('APP_URL') . '/payment/success',
        ];

        return $this->checkout->setPostData($formData)->send();
    }

    // line pay
    public function linepay($data, $req)
    {

        $linePay = new \yidas\linePay\Client([
            'channelId' => config('line_pay.LINE_PAY_CHANNEL_ID'),
            'channelSecret' => config('line_pay.LINE_PAY_CHANNEL_SECRET'),
            'isSandbox' => config('line_pay.LINE_PAY_SANDBOX')
        ]);

        $orderId = 'ORDER-ID-' . $req['orderId'];

        $order = new Order();
        $order->order_id = $orderId;
        $order->amount = $data['amount'];
        $order->status = 0;
        $order->save();

        $total = 0;
        $itemDescriptionArray = [];
        foreach ($data['products'] as $key => $value) {
            $items[$key] = [
                'name' => $value['name'],
                'quantity' => (int)$value['quantity'],
                'price' => (int)$value['price'],
            ];
            $itemDescriptionArray[] = $value['name'];
            $total += $value['total'];
        }

        $itemDescription = implode('|', $itemDescriptionArray);

        if (!empty($data['shipping_cost'])) {
            $items[count($data['products'])] = [
                'name' => '運費',
                'quantity' => 1,
                'price' => (int)$data['shipping_cost'],
            ];
        }

        $total += (int)$data['shipping_cost'];

        $orderData = [
            "amount" => $total,
            "orderId" => $orderId,
            "currency" => "TWD",
            "packages" => [
                [
                    "id" => time(),
                    "amount" => (int)$total,
                    "name" => $itemDescription,
                    "products" => $items
                ]
            ],
            'redirectUrls' => [
                'confirmUrl' => config('app.url') . '/line-pay/confirm',
                'cancelUrl' => config('app.url') . '/line-pay/cancel',
            ],
        ];

        Storage::disk('public')->put('order', json_encode($orderData));
        Storage::disk('public')->put('order-data', json_encode($data));
        Storage::disk('public')->put('order-req', json_encode($req));

        $response = $linePay->request($orderData);

        // if (!$response->isSuccessful()) {
        // throw new Exception("ErrorCode {$response['returnCode']}: {$response['returnMessage']}");
        // }

        $responseArray = $response->toArray();

        // dd($orderId, $responseArray);

        $web = $responseArray['info']['paymentUrl']['web'];
        $order = Order::where('order_id', $orderId)->first();
        $order->info = json_encode($responseArray['info']);
        $order->save();

        // Redirect to LINE Pay payment URL 
        header('Location: ' . $response->getPaymentUrl());
    }

    public function confirm(Request $request)
    {

        $req = $request->all();

        // dd($req);

        $orderId = $req['orderId'];

        $linePay = new \yidas\linePay\Client([
            'channelId' => config('line_pay.LINE_PAY_CHANNEL_ID'),
            'channelSecret' => config('line_pay.LINE_PAY_CHANNEL_SECRET'),
            'isSandbox' => config('line_pay.LINE_PAY_SANDBOX')
        ]);

        $order = Order::where('order_id', $orderId)->first();
        $order->transaction_id = $req['transactionId'];
        $order->status = 1;
        $order->save();

        // confirm
        $confirm = $linePay->confirm($req['transactionId'], [
            "amount" => $order->amount,
            "currency" => 'TWD',
        ]);

        // detail,套件寫錯，陣列已經廢除
        $detail = $linePay->details([
            "transactionId" => $req['transactionId'],
        ]);

        $detailArray = $detail->toArray();

        $apiRes = $detailArray['info'][0];

        $order->info = json_encode(
            [
                'order' => $order->info,
                'info_payment' => $apiRes
            ]
        );
        $order->save();

        if ($detailArray['returnCode'] == '0000') {
            $msg = '付款已經完成';
            $status = 'success';
        } else {
            $msg = '付款失敗';
            $status = 'fail';
        }

        // return view('linePayResult', compact('detailArray', 'msg', 'status'));
        return redirect('/payment/result?status=' . $status . '&orderId=' . $orderId);
    }

    // 付款結果
    public function paymentResult(Request $request)
    {

        $data = $request->all();

        $orderId = '';

        if ($data['RtnCode'] == 1) {
            $msg = "付款已經完成";
            $status = 'success';
            // dd($data);
            $orderId = $data['TradeNo'];
        } else {
            $msg = '付款失敗';
            $status = 'fail';
        }

        // return view('paymentResult', compact('msg', 'data', 'status'));
        return redirect('/payment/result?status=' . $status . '&orderId=' . $orderId);
    }

    public function payResult(Request $request)
    {

        $data = $request->all();

        $status = $data['status'];

        return view('paymentResult', compact('status'));
    }

    // 新加入的 order
    public function orderData(Request $request, $customerId)
    {
        $req = $request->all();

        $orderData = OrderData::updateOrCreate(
            ['customer_id' => $customerId],
            ['data' => json_encode($req, JSON_THROW_ON_ERROR)]
        );
    }
}
