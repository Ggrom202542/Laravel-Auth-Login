<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    protected $fillable = [
        'prefix',
        'name',
        'phone',
        'email',
        'username',
        'password',
        'avatar',
        'user_type'
    ];
}
