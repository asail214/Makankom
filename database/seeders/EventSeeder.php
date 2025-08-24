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
            [
                'name' => 'Demo Organizer', 
                'password' => bcrypt('password'), 
                'status' => 'verified', // Use ERD enum value
                'type' => 'company'
            ]
        );

        $brand = Brand::firstOrCreate([
            'name' => 'Demo Brand',
            'organizer_id' => $organizer->id,
        ], [
            'logo' => 'https://example.com/logos/demo-brand.png',
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
                'description' => 'A sample seeded event for testing purposes.',
                'short_description' => 'Sample demo event',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(7)->addHours(3),
                'venue_name' => 'Muscat Convention Center',
                'venue_address' => '123 Al Khuwair Street, Muscat, Oman',
                'status' => 'published',
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => Admin::first()?->id,
            ]
        );
    }
}