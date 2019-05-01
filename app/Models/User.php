<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $fillable = ['user_id', 'first_name', 'last_name', 'phone_number', 'reg_number', 'email' ];
}
