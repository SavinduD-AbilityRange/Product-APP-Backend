<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Interest;

class InterestsSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            ['name' => 'Sports', 'icon' => '⚽'],
            ['name' => 'Music', 'icon' => '🎵'],
            ['name' => 'Reading', 'icon' => '📚'],
            ['name' => 'Gaming', 'icon' => '🎮'],
            ['name' => 'Travel', 'icon' => '✈️'],
            ['name' => 'Cooking', 'icon' => '👨‍🍳'],
            ['name' => 'Photography', 'icon' => '📸'],
            ['name' => 'Art', 'icon' => '🎨'],
            ['name' => 'Technology', 'icon' => '💻'],
            ['name' => 'Fitness', 'icon' => '💪'],
        ];

        foreach ($interests as $interest) {
            Interest::create($interest);
        }
    }
}
