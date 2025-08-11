<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'category', 'price', 'image',
    ];

    
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        
        if (str_starts_with($this->image, '/storage/')) {
            return env('APP_URL') . $this->image;
        }

        
        return env('APP_URL') . '/storage/' . ltrim($this->image, '/');
    }

    
    protected $appends = ['image_url'];
}
