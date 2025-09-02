<?php
//2nd September 2025 - Created Interest model - Ashini
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'is_active',
    ];

    public function userInterests()
    {
        return $this->hasMany(UserInterest::class);
    }

    public function users()
    {
        return $this->belongsToMany(Customer::class, 'user_interests', 'interest_id', 'user_id');
    }
}
