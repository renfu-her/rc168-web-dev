<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IndexController;
use App\Http\Controllers\QrcodeController;

Route::get('/', [IndexController::class, 'index']);
Route::get('/qrcode', [QrcodeController::class, 'qrcode']);
Route::post('/upload', [IndexController::class, 'upload']);
