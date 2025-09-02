<?php
// 2nd September 2025 - Created UserAuth model - Ashini
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    use HasFactory;

    protected $table = 'userauth';

    protected $fillable = [
        'user_id',
        'user_role',
        'user_api_key',
        'user_status',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}
