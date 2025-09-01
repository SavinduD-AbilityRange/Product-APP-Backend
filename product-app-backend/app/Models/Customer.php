<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'profile_image', 'first_name', 'middle_name', 'last_name', 'dob', 'address', 'status', 'role', 'email', 'password', 'otp', 'otp_expires_at', 'parent_id'
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
}