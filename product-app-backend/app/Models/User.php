<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'dob',
        'address',
        'profile_picture',
        'parent_email',
        'otp',
        'otp_expires_at',
        'is_verified',
        'parent_otp',
        'parent_otp_expires_at',
        'is_parent_verified',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'otp',
        'otp_expires_at',
        'parent_otp',
        'parent_otp_expires_at',
    ];
}
