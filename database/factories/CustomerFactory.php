<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => '+968-' . fake()->numerify('9###-####'),
            'date_of_birth' => fake()->date('Y-m-d', '2000-01-01'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Muscat', 'Salalah', 'Nizwa', 'Sur', 'Sohar']),
            'state' => fake()->randomElement(['Muscat Governorate', 'Dhofar Governorate', 'Al Dakhiliyah Governorate']),
            'country' => 'Oman',
            'postal_code' => fake()->numerify('###'),
            'preferred_language' => fake()->randomElement(['en', 'ar']),
            'status' => 'active',
            'notification_preferences' => [
                'email_notifications' => fake()->boolean(),
                'sms_notifications' => fake()->boolean(),
                'marketing_emails' => fake()->boolean()
            ],
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}