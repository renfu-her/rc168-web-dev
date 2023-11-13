<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserTokenController;
use App\Http\Controllers\Api\CaseClientController;
use App\Http\Controllers\Api\CaseJoinController;

Route::post('/user_token/check', [UserTokenController::class, 'store'])->name('user.token.store');

Route::group(['prefix' => 'user'], function () {
    Route::post('/client/write', [CaseClientController::class, 'store'])->name('user.client.store');
    Route::post('/client/view', [CaseClientController::class, 'view'])->name('user.client.view');
    Route::get('/client/getAll', [CaseJoinController::class, 'getAll'])->name('user.join.getAll');

    Route::post('/join/view', [CaseJoinController::class, 'view'])->name('user.join.view');
});
