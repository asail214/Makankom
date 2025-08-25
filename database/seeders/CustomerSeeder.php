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
                'password' => Hash::make('password'),
                'phone' => '+968-9123-4567',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'address' => '123 Sultan Qaboos Street',
                'city' => 'Muscat',
                'state' => 'Muscat Governorate',
                'country' => 'Oman',                'preferred_language' => 'ar',
                'status' => 'active',
                'notification_preferences' => [
                    'email_notifications' => true,
                    'sms_notifications' => true,
                    'marketing_emails' => false
                ]
            ]
        );

        // Add another test customer
        Customer::updateOrCreate(
            ['email' => 'sara@example.com'],
            [
                'first_name' => 'Sara',
                'last_name' => 'Al-Zahra',
                'email' => 'sara@example.com',
                'password' => Hash::make('password'),
                'phone' => '+968-9876-5432',
                'date_of_birth' => '1995-08-22',
                'gender' => 'female',
                'address' => '456 Al Khuwair Street',
                'city' => 'Muscat',
                'state' => 'Muscat Governorate',
                'country' => 'Oman',
                'preferred_language' => 'en',
                'status' => 'active',
                'notification_preferences' => [
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'marketing_emails' => true
                ]
            ]
        );
    }
}