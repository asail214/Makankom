<?php

namespace Database\Seeders;

use App\Models\Organizer;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizerSeeder extends Seeder
{
    public function run(): void
    {
        Organizer::updateOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Ahmed Al-Hashmi Events',
                'email' => 'organizer@example.com',
                'password' => Hash::make('password'),
                'phone' => '+968-9555-1234',
                'type' => 'company',
                'profile_img_url' => 'https://example.com/profiles/organizer1.jpg',
                'cr_number' => 'CR123456789',
                'status' => 'verified',
                'approved_by' => Admin::first()?->id,
                'approved_at' => now(),
            ]
        );

        Organizer::updateOrCreate(
            ['email' => 'individual@example.com'],
            [
                'name' => 'Fatima Al-Zahra',
                'email' => 'individual@example.com',
                'password' => Hash::make('password'),
                'phone' => '+968-9777-8888',
                'type' => 'individual',
                'profile_img_url' => 'https://example.com/profiles/individual1.jpg',
                'cr_number' => null, // Individual may not need CR
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]
        );
    }
}