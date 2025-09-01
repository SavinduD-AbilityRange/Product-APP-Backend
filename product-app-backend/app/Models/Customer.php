<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//1st September 2025 - Created the customer model - Ashini 19:40

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'date_of_birth',
        'role',
        'parent_id',
        'otp_code',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}
