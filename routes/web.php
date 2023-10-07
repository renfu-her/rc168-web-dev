<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\QRCodeController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/upload/image', [ImageUploadController::class, 'imageUpload'])->name('image.upload');
Route::get('/qrcode-scanner', [QRCodeController::class, 'QRCodeScanner'])->name('qrcode.scanner');
