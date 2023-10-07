<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImageUploadController extends Controller
{

    public function imageUpload(Request $request)
    {

        try {
            // 驗證上傳的檔案是否有效
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (Validator $validator) {
            throw new HttpResponseException(
                response()->json([
                    'status' => 'failed',
                    'errors' => $validator->errors(),
                ], 422)
            );
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
