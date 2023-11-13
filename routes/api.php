<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserTokenController;

Route::post('/user_token/check', [UserTokenController::class, 'store'])->name('user.token.store');