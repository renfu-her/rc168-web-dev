<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserTokenController;
use App\Http\Controllers\Api\CaseClientController;
use App\Http\Controllers\Api\CaseJoinController;
use App\Http\Controllers\Api\WorkController;

Route::post('/user_token/check', [UserTokenController::class, 'store'])->name('user.token.store');

Route::group(['prefix' => 'user'], function () {
    Route::group(['prefix' => 'client'], function () {
        Route::post('/write', [CaseClientController::class, 'store'])->name('user.client.store');
        Route::post('/view', [CaseClientController::class, 'view'])->name('user.client.view');
        Route::get('/getAll', [CaseClientController::class, 'getAll'])->name('user.join.getAll');
    });
    
    Route::get('/case-detail', [WorkController::class, 'index'])->name('user.work.index');

    Route::post('/join/view', [CaseJoinController::class, 'view'])->name('user.join.view');
});
