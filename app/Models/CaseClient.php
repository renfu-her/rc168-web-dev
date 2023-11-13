<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseClient extends Model
{
    use HasFactory;

    protected $table = 'case_clients';
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'start_date',
        'end_date',
        'mobile',
        'pay',
        'status'
    ];
}
