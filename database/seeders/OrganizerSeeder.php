<?php

namespace Database\Seeders;

use App\Models\Organizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizerSeeder extends Seeder
{
    public function run(): void
    {
        Organizer::updateOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Demo Organizer',
                'password' => Hash::make('password'),
                'status' => 'active',
                'business_name' => 'Demo Events Co.',
            ]
        );
    }
}


