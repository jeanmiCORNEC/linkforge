<?php

namespace Tests\Feature\Middleware;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ForceHttpsTest extends TestCase
{
    public function test_http_requests_are_redirected_when_force_enabled(): void
    {
        Config::set('app.force_https', true);

        $response = $this->get('/links');

        $response->assertStatus(301);
        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://', $location);
        $this->assertStringEndsWith('/links', $location);
    }
}
