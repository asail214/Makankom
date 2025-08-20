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
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
    }
}


