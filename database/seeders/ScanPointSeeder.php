<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScanPoint;

class ScanPointSeeder extends Seeder
{
    public function run(): void
    {
        ScanPoint::create([
            'label' => 'Main Entrance Scanner',
            'event_id' => 1, 
            'device_information' => 'iPad Pro - iOS 17.0',
        ]);
    }
}