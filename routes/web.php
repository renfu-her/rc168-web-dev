<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductPaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\GoogleController;


// use App\Http\Controllers\EcpayController;



route::get('/', [HomeController::class, 'index']);

route::post('/upload/image', [ImageUploadController::class, 'imageUpload'])->name('image.upload');
// Route::get('/qrcode-scanner', [QRCodeController::class, 'QRCodeScanner'])->name('qrcode.scanner');

// Route::get('/ecpay', [EcpayController::class, 'index']);

route::get('/product/content/{id}', [ProductController::class, 'content']);

route::post('/ecpay/return', [ProductPaymentController::class, 'paymentResult'])->name('ecpay.return');

route::group(['prefix' => '/line-pay'], function () {
    route::get('/confirm', [ProductPaymentController::class, 'confirm']);
    route::get('/cancel', [ProductPaymentController::class, 'cancel']);
    // Route::post('/', [ProductPaymentController::class, 'index']);
    route::post('/', [ProductPaymentController::class, 'linepay']);
    route::post('/refund', [ProductPaymentController::class, 'refund']);
});

route::get('/payment/result', [ProductPaymentController::class, 'payResult'])->name('payment.result');

Route::get('/google/drive', [GoogleController::class, 'drive']);
