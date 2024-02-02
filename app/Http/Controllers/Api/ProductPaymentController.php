<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;
use Illuminate\Support\Facades\Storage;

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

        if($data['payment_method'] == 'ecpaypayment'){
            $this->ecpay($data);
        } elseif($data['payment_method'] == 'linepay_sainent'){
            $this->linepay($data);
        }

        
    }

    // ecpay
    public function ecpay($data)
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
    public function linepay($data)
    {


        dd($data);
    }

    // 付款結果
    public function paymentResult(Request $request)
    {

        $data = $request->all();

        if($data['RtnCode'] == 1){
            $msg = '付款已經完成';
        } else {
            $msg = '付款失敗';
        }

        return view('paymentResult', compact('msg', 'data'));
    }
}
