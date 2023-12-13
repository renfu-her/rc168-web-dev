<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusController extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon',
        'title',
        'sub_title',
        'expiry_date'  
    ];
}
