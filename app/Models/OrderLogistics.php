<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLogistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'logistics_sub_type',
        'cvs_store_iD',
        'cvs_store_name',
        'cvs_address',
        'cvs_telephone',
        'cvs_out_side',
    ];
}
