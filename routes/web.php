<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EcpayController;
use App\Http\Controllers\SendMailController;

Route::get('/', [HomeController::class, 'index']);

// Route::post('/upload/image', [ImageUploadController::class, 'imageUpload'])->name('image.upload');
// Route::get('/qrcode-scanner', [QRCodeController::class, 'QRCodeScanner'])->name('qrcode.scanner');

Route::get('/ecpay', [EcpayController::class, 'index']);
Route::get('/send_mail', [SendMailController::class, 'send']);
