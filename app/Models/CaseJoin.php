<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseJoin extends Model
{
    use HasFactory;

    protected $table = 'case_joins';
    protected $fillable = [
        'user_id',
        'payment',
        'user_join_id',
        'case_client_id',
        'payment',
        'status',
    ];
}
