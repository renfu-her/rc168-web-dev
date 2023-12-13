<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBonus extends Model
{
    use HasFactory;

    protected $table = 'user_bonus';

    protected $fillable = [
        'user_id',
        'bonus_id',
        'coins'
    ];


}
