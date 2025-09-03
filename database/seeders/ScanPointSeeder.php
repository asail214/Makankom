<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScanPoint;
use App\Models\Event;

class ScanPointSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first available event
        $event = Event::first();
        
        if ($event) {
            ScanPoint::create([
                'label' => 'Main Entrance Scanner',
                'event_id' => $event->id, // Use the actual event ID
                'device_information' => 'iPad Pro - iOS 17.0',
                'status' => 'active',
                'location' => 'Main Entrance'
            ]);
        }
    }
}