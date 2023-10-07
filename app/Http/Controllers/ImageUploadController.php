<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class ImageUploadController extends Controller
{

    public function imageUpload(Request $request)
    {

        try {
            // 驗證上傳的檔案是否有效
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'errors' => $e->errors(),
            ]);
        }

        // 取得上傳的檔案
        $image = $request->file('image');

        // 建立一個唯一的檔案名稱
        $filename = uniqid('image_') . '.' . $image->getClientOriginalExtension();

        // 將檔案儲存到 storage/app/public/images 目錄下
        $image->storeAs('public/upload/images', $filename);

        // 取得儲存路徑
        $imagePath = env('APP_URL') . '/images/' . $filename;

        // 回傳成功訊息及儲存路徑
        return response()->json([
            'status' => 'success',

            'path' => $imagePath,
        ], 200);;
    }
}
