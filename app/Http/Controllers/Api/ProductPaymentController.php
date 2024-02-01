<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;

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

        $data = $request->all();

        $addressId = $data['address_id'];
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


        $formData = [
            'itemDescription' => $itemDescription,
            'items' => $items,
            'paymentMethod' => 'Credit',
            'userId' => $customerId
        ];

        dd($formData);

        return $this->checkout->setPostData($formData);
    }
}
