<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserTokenController;
use App\Http\Controllers\Api\CaseClientController;
use App\Http\Controllers\Api\CaseJoinController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\JoinWriteController;
use App\Http\Controllers\Api\CoinController;
use App\Http\Controllers\Api\UserBonusController;
use App\Http\Controllers\Api\BonusController;
use App\Http\Controllers\Api\ProductDetailController;
use App\Http\Controllers\Api\ProductPaymentController;

use App\Http\Controllers\SendMailController;

Route::post('/user_token/check', [UserTokenController::class, 'store'])->name('user.token.store');

Route::group(['prefix' => 'user'], function () {
    Route::group(['prefix' => 'client'], function () {
        Route::post('/write', [CaseClientController::class, 'store'])->name('user.client.store');
        Route::post('/view', [CaseClientController::class, 'view'])->name('user.client.view');
        Route::get('/getAll', [CaseClientController::class, 'getAll'])->name('user.client.getAll');
    });

    Route::group(['prefix' => 'join'], function () {
        Route::post('/write', [JoinWriteController::class, 'store'])->name('user.join.store');
        Route::post('/view', [JoinWriteController::class, 'view'])->name('user.join.view');
        Route::post('/getAll', [JoinWriteController::class, 'getAll'])->name('user.join.getAll');
    });

    Route::get('/case-detail', [WorkController::class, 'index'])->name('user.work.index');
    Route::post('/do-case', [WorkController::class, 'doCase'])->name('user.work.doCase');
    Route::post('/case-to-confirm', [WorkController::class, 'caseToConfirm'])->name('user.work.caseToConfirm');

    Route::post('/set-status', [CaseClientController::class, 'setStatus'])->name('user.client.setStatus');

    Route::get('/coin', [CoinController::class, 'index']);

    Route::post('/bonus/save', [UserBonusController::class, 'save']);
    Route::get('/bonus/getAll', [UserBonusController::class, 'getAll']);

    Route::get('/bonus', [BonusController::class, 'index']);
    Route::post('/bonus', [BonusController::class, 'store']);
});

Route::post('/send_mail', [SendMailController::class, 'send']);

// 另外的 API
Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
    Route::get('/detail/{id}', [ProductDetailController::class, 'detail'])->name('detail');
    Route::get('/detail/content/{id}', [ProductDetailController::class, 'getContent'])->name('detail.content');
    Route::get('/submit/{customerId}', [ProductDetailController::class, 'setOrder'])->name('submit');
    Route::get('/payment', [ProductPaymentController::class, 'payment'])->name('payment');
    Route::post('/order/data/{customerId}', [ProductPaymentController::class, 'orderData'])->name('order.data');
});
