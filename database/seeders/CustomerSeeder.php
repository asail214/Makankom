<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Rashid',
                'email' => 'customer@example.com',
                'phone' => '+968-9123-4567',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        Customer::updateOrCreate(
            ['email' => 'sara@example.com'],
            [
                'first_name' => 'Sara',
                'last_name' => 'Al-Zahra',
                'email' => 'sara@example.com',
                'phone' => '+968-9876-5432',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
    }
}