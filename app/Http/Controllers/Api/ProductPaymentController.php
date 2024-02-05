<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;
use Illuminate\Support\Facades\Storage;
use Exception;


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

        $total = 0;
        $itemDescription = '';
        foreach ($data['products'] as $key => $value) {
            $items[$key] = [
                'name' => $value['name'],
                'quantity' => $value['quantity'],
                'price' => $value['price'],
            ];
            $itemDescription .= $value['name'] . '|';
            $total += $value['total'];
        }

        if (!empty($data['shipping_cost'])) {
            $items[count($data['products']) + 1] = [
                'name' => '運費',
                'quantity' => 1,
                'price' => $data['shipping_cost'],
            ];
        }

        $total += $data['shipping_cost'];

        $order = [
            "amount" => $total,
            "orderId" => 'OID-' . date('YmdHis') . '-' . $req['orderId'],
            "currency" => "TWD",
            "packages" => [
                [
                    "id" => "0001",
                    "amount" => $total,
                    "name" => $itemDescription,
                    "products" => $items
                ]
            ],
            'redirectUrls' => [
                'confirmUrl' => config('app.url') . '/line-pay/confirm',
                'cancelUrl' => config('app.url') . '/line-pay/cancel',
            ],
        ];


        $linePay = new \yidas\linePay\Client([
            'channelId' => config('line_pay.LINE_PAY_CHANNEL_ID'),
            'channelSecret' => config('line_pay.LINE_PAY_CHANNEL_SECRET'),
            'isSandbox' => config('line_pay.LINE_PAY_SANDBOX')
        ]);

        $request = $linePay->request($order);

        dd($order, $request->isSuccessful());

        if (!$request->isSuccessful()) {
            throw new Exception("ErrorCode {$request['returnCode']}: {$request['returnMessage']}");
        }




        dd($request->getPaymentUrl());
        // Redirect to LINE Pay payment URL 
        header('Location: ' . $request->getPaymentUrl());
    }

    public function confirm(Request $request)
    {

        $req = $request->all();

        $linePay = new \yidas\linePay\Client([
            'channelId' => config('line_pay.LINE_PAY_CHANNEL_ID'),
            'channelSecret' => config('line_pay.LINE_PAY_CHANNEL_SECRET'),
            'isSandbox' => config('line_pay.LINE_PAY_SANDBOX')
        ]);

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
    }

    // 付款結果
    public function paymentResult(Request $request)
    {

        $data = $request->all();

        if ($data['RtnCode'] == 1) {
            $msg = '付款已經完成';
        } else {
            $msg = '付款失敗';
        }

        return view('paymentResult', compact('msg', 'data'));
    }
}
