<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function clientToken(): string
    {
        return User::factory()->create(['role' => 'client'])->createToken('auth_token')->plainTextToken;
    }

    protected function adminToken(): string
    {
        return User::factory()->create(['role' => 'admin'])->createToken('auth_token')->plainTextToken;
    }

    public function test_client_cannot_create_membership_plan(): void
    {
        $this->postJson('/api/membership-plans', [
            'name' => 'Plan Pro',
            'price' => 19.99,
            'duration_days' => 30,
        ], ['Authorization' => 'Bearer '.$this->clientToken()])
            ->assertStatus(403);
    }

    public function test_admin_can_create_membership_plan(): void
    {
        $this->postJson('/api/membership-plans', [
            'name' => 'Plan Pro',
            'price' => 19.99,
            'duration_days' => 30,
        ], ['Authorization' => 'Bearer '.$this->adminToken()])
            ->assertStatus(201);
    }

    public function test_public_can_view_membership_plans(): void
    {
        MembershipPlan::create(['name' => 'Basico', 'price' => 9.99, 'duration_days' => 30]);

        $this->getJson('/api/membership-plans')->assertOk();
    }

    public function test_client_only_sees_own_subscriptions(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $other = User::factory()->create(['role' => 'client']);
        $plan = MembershipPlan::create(['name' => 'Basico', 'price' => 9.99, 'duration_days' => 30]);

        Subscription::create([
            'user_id' => $client->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'next_billing_date' => now()->addDays(30),
        ]);

        Subscription::create([
            'user_id' => $other->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'next_billing_date' => now()->addDays(30),
        ]);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->getJson('/api/subscriptions', ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonCount(1);
    }
}
