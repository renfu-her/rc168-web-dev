<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

Route::post('/upload/image', [ImageUploadController::class, 'imageUpload'])->name('image.upload');
Route::get('/qrcode-scanner', [QRCodeController::class, 'QRCodeScanner'])->name('qrcode.scanner');
