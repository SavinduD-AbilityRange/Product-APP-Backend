<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    protected $table = 'userauth';
    
    protected $fillable = [
        'user_id',
        'user_role',
        'user_api_key',
        'user_status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}
