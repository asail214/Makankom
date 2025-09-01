<?php

namespace Tests\Feature;

use Tests\TestCase;

class SimpleAuthTest extends TestCase
{
    public function test_protected_endpoints_require_authentication()
    {
        // Try to access protected endpoint without token
        $response = $this->get('/api/customer/profile');
        
        // Debug: Show what error we're getting
        if ($response->status() === 500) {
            $content = $response->getContent();
            echo "Error content: " . $content;
        }
        
        // Should get 401 unauthorized
        $this->assertEquals(401, $response->status());
    }
} 