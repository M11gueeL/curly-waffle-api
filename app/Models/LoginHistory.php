<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id',
        'token_id',
        'login_at',
        'logout_at',
        'status',
    ];
}
