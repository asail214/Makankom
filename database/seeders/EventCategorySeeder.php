<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Music', 'icon' => 'music', 'color' => '#6C5CE7'],
            ['name' => 'Sports', 'icon' => 'sports', 'color' => '#00B894'],
            ['name' => 'Conference', 'icon' => 'work', 'color' => '#0984E3'],
            ['name' => 'Workshop', 'icon' => 'school', 'color' => '#E17055'],
            ['name' => 'Festival', 'icon' => 'festival', 'color' => '#D63031'],
        ];

        foreach ($categories as $index => $cat) {
            EventCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['name'].' events and activities',
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}


