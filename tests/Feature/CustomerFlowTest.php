<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Event;

class CustomerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_login_event_selection_logout()
    {
        // Registration
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            // Add other required fields
        ]);
        $response->assertStatus(302); // or 201 for API

        // Login
        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(302); // or 200 for API

        // Simulate event selection (assuming events exist)
        $event = Event::factory()->create();
        $customer = Customer::where('email', 'testuser@example.com')->first();
        $this->actingAs($customer);
        $response = $this->post("/events/{$event->id}/register");
        $response->assertStatus(200);

        // Logout
        $response = $this->post('/logout');
        $response->assertStatus(302); // or 200 for API

        // Re-login
        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(302); // or 200 for API
    }
}