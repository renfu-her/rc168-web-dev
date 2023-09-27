<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IndexController;
use App\Http\Controllers\QrcodeController;
use App\Http\Controllers\TakePictureController;

Route::get('/', [IndexController::class, 'index']);
Route::get('/qrcode', [QrcodeController::class, 'qrcode']);
Route::get('/take-picture', [TakePictureController::class, 'takePicture']);
Route::post('/upload', [IndexController::class, 'upload']);
