<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseJoin extends Model
{
    use HasFactory;

    protected $table = 'case_joins';
    protected $fillable = [
        'case_id',
        'user_id',
        'payment',
    ];
}
