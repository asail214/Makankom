<?php

namespace Tests\Feature;

use Tests\TestCase;

class SimpleAuthTest extends TestCase
{
    public function test_basic_authentication_endpoints_exist()
    {
        // Test that login endpoints exist
        $response = $this->post('/api/customer/login');
        // Should get validation error (not 404)
        $this->assertNotEquals(404, $response->status());
        
        $response = $this->post('/api/admin/login');
        // Should get validation error (not 404)  
        $this->assertNotEquals(404, $response->status());
    }

    public function test_protected_endpoints_require_authentication()
    {
        // Try to access protected endpoint without token
        $response = $this->get('/api/customer/profile');
        // Should get 401 unauthorized
        $this->assertEquals(401, $response->status());
        
        $response = $this->get('/api/admin/profile');  
        // Should get 401 unauthorized
        $this->assertEquals(401, $response->status());
    }
}