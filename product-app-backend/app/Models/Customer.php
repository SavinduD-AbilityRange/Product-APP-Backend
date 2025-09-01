<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';

    protected $fillable = [
        'profile_image',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'address',
        'status',
        'role',
        'email',
        'password',
        'email_verified',
        'otp_code',
        'parent_id',
    ];

    protected $hidden = [
        'password',
        'otp_code',
    ];
}
