<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations and seeders for testing
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed --class=AdminSeeder');
        $this->artisan('db:seed --class=CustomerSeeder');
    }

    public function test_customer_token_cannot_access_admin_endpoints()
    {
        // Login as customer
        $response = $this->postJson('/api/customer/login', [
            'email' => 'customer@example.com',
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        $token = $response->json('data.token');
        
        // Try to access admin endpoint with customer token
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->get('/api/admin/events');
        
        // Should be forbidden (403) or unauthorized (401)
        $this->assertTrue(in_array($response->status(), [401, 403]));
    }

    public function test_admin_token_cannot_access_customer_endpoints()
    {
        // Login as admin
        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        $token = $response->json('data.token');
        
        // Try to access customer endpoint with admin token
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->get('/api/customer/orders');
        
        // Should be forbidden (403) or unauthorized (401)
        $this->assertTrue(in_array($response->status(), [401, 403]));
    }

    public function test_customer_can_access_own_endpoints()
    {
        // Login as customer
        $response = $this->postJson('/api/customer/login', [
            'email' => 'customer@example.com',
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        $token = $response->json('data.token');
        
        // Try to access customer profile (should work)
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->get('/api/customer/profile');
        
        // Should be successful (200)
        $response->assertStatus(200);
    }

    public function test_admin_can_access_own_endpoints()
    {
        // Login as admin
        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);
        
        $response->assertStatus(200);
        $token = $response->json('data.token');
        
        // Try to access admin profile (should work)
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->get('/api/admin/profile');
        
        // Should be successful (200)
        $response->assertStatus(200);
    }
}