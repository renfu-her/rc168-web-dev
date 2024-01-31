<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;

// use App\Http\Controllers\EcpayController;



route::get('/', [HomeController::class, 'index']);

route::post('/upload/image', [ImageUploadController::class, 'imageUpload'])->name('image.upload');
// Route::get('/qrcode-scanner', [QRCodeController::class, 'QRCodeScanner'])->name('qrcode.scanner');

// Route::get('/ecpay', [EcpayController::class, 'index']);

route::get('/product/content/{id}', [ProductController::class, 'content']);

route::post('/test/url', [TestController::class, 'url']);

route::get('/test/pdf', function(){
    return view('pdf');
});
