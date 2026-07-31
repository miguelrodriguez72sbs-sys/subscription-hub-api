<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendTest extends TestCase
{
    public function test_frontend_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Subscription Hub')
            ->assertSee("const API = '/api'", false)
            ->assertSee('sh_token');
    }
}
