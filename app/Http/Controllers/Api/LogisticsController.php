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

        if (!empty($logistics)) {
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

    public function rewrite(Request $request)
    {
        $res = $request->all();

        $memberId = $res['memberId'];

        OrderLogistics::updateOrCreate([
            'member_id' => $memberId
        ], [
            'logistics_sub_type' => $res['LogisticsSubType'],
            'cvs_store_id' => $res['CVSStoreID'],
            'cvs_store_name' => $res['CVSStoreName'],
            'cvs_address' => $res['CVSAddress'],
            'cvs_telephone' => $res['CVSTelephone'],
            'cvs_out_side' => $res['CVSOutSide']
        ]);

        return view('remove');
    }

    public function logisticsRemove(Request $request, $memberId)
    {
        OrderLogistics::where('member_id', $memberId)->delete();

        // return view('remove');
    }
}
