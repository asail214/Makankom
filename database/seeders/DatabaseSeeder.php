<?php

namespace Database\Seeders;

use App\Models\Customer;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EventCategorySeeder::class,
            AdminSeeder::class,
            CustomerSeeder::class,
            OrganizerSeeder::class,
            EventSeeder::class,
            TicketTypeSeeder::class,
        ]);
    }
}
