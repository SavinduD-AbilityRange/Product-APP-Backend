<?php
// 2nd September 2025 - Created UserInterest model - Ashini
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'interest_id',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function interest()
    {
        return $this->belongsTo(Interest::class);
    }
}
