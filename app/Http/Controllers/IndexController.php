<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        return view('index');
    }

    // upload image
    public function upload(Request $request)
    {
        $image = $request->file('image');
        dd($request->all());

        $qrImage = ''; // 這裡是你的 QRCode 圖片

        // 使用 GD 或 Imagick 來合成圖片
        $combinedImage = $this->combineImages($image, $qrImage);

        // // 儲存或回傳合成後的圖片
        // $combinedImage->save('path_to_save_image.jpg');

        // return response()->download('path_to_save_image.jpg');
    }

    function combineImages($image1, $image2)
    {
        // 這裡是你的圖片合成邏輯
    }
}
