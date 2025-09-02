<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'profile_image', 'first_name', 'middle_name', 'last_name', 'dob', 'address', 'description', 'status', 'role', 'email', 'password', 'otp', 'otp_expires_at', 'parent_id', 'is_verified', 'parent_email', 'parent_otp', 'parent_otp_expires_at', 'is_parent_verified', 'profile_picture'
    ];

    protected $hidden = ['password', 'otp'];

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function parent()
    {
        return $this->belongsTo(Customer::class, 'parent_id');
    }

    public function userAuth()
    {
        return $this->hasOne(UserAuth::class, 'user_id');
    }
}