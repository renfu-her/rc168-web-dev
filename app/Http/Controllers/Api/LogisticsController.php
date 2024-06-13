<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderLogistics;

class LogisticsController extends Controller
{
    
    public function logistics(Request $request)
    {
        $res = $request->all();

        $memberId = $res['memberId'];

        $logistics = OrderLogistics::where('member_id', $memberId)->first();

        if(!empty($logistics)){
            return response()->json([
                'status' => 'success',
                'data' => $logistics
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'data' => []
        ]);
        

    }
}
