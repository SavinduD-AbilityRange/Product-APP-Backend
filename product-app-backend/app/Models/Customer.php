<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'dob', 'address', 'description', 'status', 'role', 'email', 'password', 'otp', 'otp_expires_at', 'parent_id', 'is_verified', 'parent_email', 'parent_otp', 'parent_otp_expires_at', 'is_parent_verified', 'profile_picture', 'children'
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

    /**
     * Get the next available ID (reuses IDs from deleted records)
     */
    public static function getNextAvailableId()
    {
        $existingIds = self::pluck('id')->toArray();
        
        for ($i = 1; $i <= count($existingIds) + 1; $i++) {
            if (!in_array($i, $existingIds)) {
                return $i;
            }
        }
        
        return count($existingIds) + 1;
    }

    /**
     * Override the create method to use available IDs
     */
    public static function create(array $attributes = [])
    {
        $nextId = self::getNextAvailableId();
        
        $attributes['id'] = $nextId;
        
        $instance = new static($attributes);
        $instance->id = $nextId;
        $instance->save();
        
        return $instance;
    }
}