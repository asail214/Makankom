<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Organizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = Organizer::firstOrCreate(
            ['email' => 'organizer@example.com'],
            ['name' => 'Demo Organizer', 'password' => bcrypt('password'), 'status' => 'active']
        );

        $brand = Brand::firstOrCreate([
            'slug' => 'demo-brand',
        ], [
            'organizer_id' => $organizer->id,
            'name' => 'Demo Brand',
            'is_active' => true,
        ]);

        $category = EventCategory::first();
        if (!$category) {
            $category = EventCategory::create([
                'name' => 'General',
                'slug' => 'general',
                'is_active' => true,
            ]);
        }

        Event::updateOrCreate(
            ['slug' => 'demo-event'],
            [
                'organizer_id' => $organizer->id,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'title' => 'Demo Event',
                'description' => 'A sample seeded event.',
                'short_description' => 'Sample',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(7)->addHours(3),
                'venue_name' => 'Demo Venue',
                'venue_address' => '123 Demo Street',
                'status' => 'published',
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => Admin::first()?->id,
            ]
        );
    }
}


