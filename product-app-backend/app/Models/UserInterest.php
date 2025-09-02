<?php
//[02/09/2025 |Asmitha T| 11.11] - User Interest Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    protected $fillable = ['user_id', 'interest_id'];
}
