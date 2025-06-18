<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_device_gets_warning_page(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_6 like Mac OS X)'
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Acceso temporalmente restringido');
    }

    public function test_desktop_device_can_access_normally(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36'
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Acceso temporalmente restringido');
    }
}
