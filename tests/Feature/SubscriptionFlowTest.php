<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_subscribe_without_sending_user_id(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $plan = MembershipPlan::create(['name' => 'Pro', 'price' => 19.99, 'duration_days' => 30]);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions', [
            'membership_plan_id' => $plan->id,
        ], ['Authorization' => "Bearer $token"])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $client->id)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $client->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_subscribe_for_a_specific_user(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $plan = MembershipPlan::create(['name' => 'Pro', 'price' => 19.99, 'duration_days' => 30]);

        $token = $admin->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions', [
            'user_id' => $client->id,
            'membership_plan_id' => $plan->id,
        ], ['Authorization' => "Bearer $token"])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $client->id);
    }
}
