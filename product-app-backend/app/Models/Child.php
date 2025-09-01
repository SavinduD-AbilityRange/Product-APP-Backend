<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    protected $fillable = [
        'customer_id', 'profile_image', 'first_name', 'middle_name', 'last_name', 'dob', 'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}