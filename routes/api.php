<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageUploadController;

Route::post('/upload/image', [ImageUploadController::class, 'imageUpload'])->name('image.upload');
