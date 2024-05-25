<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Models\Order;
use App\Models\OrderData;


class ProductPaymentController extends Controller
{

    protected $checkout;
    public function __construct(Checkout $checkout)
    {
        $this->checkout = $checkout;
    }
    // index
    public function payment(Request $request)
    {

        $req = $request->all();

        $content = Storage::disk('public')->get($req['customerId'] . '.txt');

        $data = json_decode($content, true);

        if ($data['payment_method'] == 'ecpaypayment') {
            return $this->ecpay($data, $req);
        } elseif ($data['payment_method'] == 'linepay_sainent') {
            return $this->linepay($data, $req);
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

        $orderId = 'OID-' . $req['orderId'];

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

        $order = [
            "amount" => $total,
            "orderId" => $orderId,
            "currency" => "TWD",
            "packages" => [
                [
                    "id" => "0001",
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

        Storage::disk('public')->put('order', json_encode($order));
        Storage::disk('public')->put('order-data', json_encode($data));
        Storage::disk('public')->put('order-req', json_encode($req));

        $response = $linePay->request($order);

        // if (!$response->isSuccessful()) {
        // throw new Exception("ErrorCode {$response['returnCode']}: {$response['returnMessage']}");
        // }

        $responseArray = $response->toArray();

        // dd($responseArray);

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
    public function orderData(Request $request, $orderId)
    {
        $req = $request->all();

        OrderData::create([
            'order_no' => $orderId,
            'data' => json_encode($req)
        ]);
    }
}
