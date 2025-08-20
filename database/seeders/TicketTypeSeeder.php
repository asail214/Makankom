<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::where('slug', 'demo-event')->first();
        if (!$event) {
            return;
        }

        $types = [
            ['name' => 'General Admission', 'price' => 50, 'quantity_available' => 500],
            ['name' => 'VIP', 'price' => 150, 'quantity_available' => 100, 'benefits' => ['Lounge access','Priority entry']],
        ];

        foreach ($types as $t) {
            TicketType::updateOrCreate(
                ['event_id' => $event->id, 'name' => $t['name']],
                [
                    'description' => $t['name'].' ticket',
                    'price' => $t['price'],
                    'quantity_available' => $t['quantity_available'],
                    'quantity_sold' => 0,
                    'is_active' => true,
                    'benefits' => $t['benefits'] ?? null,
                ]
            );
        }
    }
}


