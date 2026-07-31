<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nuevo Cliente',
            'email' => 'nuevo@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com', 'role' => 'client']);
    }

    public function test_login_returns_token(): void
    {
        User::factory()->create(['email' => 'cliente@example.com', 'password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/login', [
            'email' => 'cliente@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nadie@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer $token"])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->getJson('/api/user', ['Authorization' => "Bearer $token"])->assertStatus(401);
    }
}
