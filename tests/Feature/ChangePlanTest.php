<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangePlanTest extends TestCase
{
    use RefreshDatabase;

    private function plans(): array
    {
        $basic = MembershipPlan::create(['name' => 'Basica', 'application' => 'Netflix', 'price' => 6.99, 'duration_days' => 30]);
        $premium = MembershipPlan::create(['name' => 'Premium', 'application' => 'Netflix', 'price' => 15.99, 'duration_days' => 30]);
        $spotify = MembershipPlan::create(['name' => 'Individual', 'application' => 'Spotify', 'price' => 5.99, 'duration_days' => 30]);

        return [$basic, $premium, $spotify];
    }

    private function makeSubscription(User $user, MembershipPlan $plan): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'next_billing_date' => now()->addDays(30),
        ]);
    }

    public function test_upgrade_charges_difference_and_updates_plan(): void
    {
        [$basic, $premium] = $this->plans();
        $client = User::factory()->create(['role' => 'client']);
        $sub = $this->makeSubscription($client, $basic);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions/' . $sub->id . '/change-plan', [
            'membership_plan_id' => $premium->id,
            'payment_method' => 'card',
            'simulate_decision' => 'approved',
        ], ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonPath('amount_charged', 9)
            ->assertJsonPath('payment_method', 'card')
            ->assertJsonPath('subscription.plan_id', $premium->id);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'membership_plan_id' => $premium->id,
        ]);

        $this->assertDatabaseHas('invoices', [
            'subscription_id' => $sub->id,
            'amount' => 9.0,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'status' => 'succeeded',
            'payment_method' => 'card',
        ]);
    }

    public function test_downgrade_updates_plan_without_charge(): void
    {
        [$basic, $premium] = $this->plans();
        $client = User::factory()->create(['role' => 'client']);
        $sub = $this->makeSubscription($client, $premium);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions/' . $sub->id . '/change-plan', [
            'membership_plan_id' => $basic->id,
            'payment_method' => 'paypal',
        ], ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonPath('amount_charged', -9)
            ->assertJsonPath('subscription.plan_id', $basic->id);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'membership_plan_id' => $basic->id,
        ]);

        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_cannot_change_to_plan_of_another_application(): void
    {
        [$basic, , $spotify] = $this->plans();
        $client = User::factory()->create(['role' => 'client']);
        $sub = $this->makeSubscription($client, $basic);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions/' . $sub->id . '/change-plan', [
            'membership_plan_id' => $spotify->id,
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJsonValidationErrors('membership_plan_id');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'membership_plan_id' => $basic->id,
        ]);
    }

    public function test_declined_payment_does_not_modify_plan(): void
    {
        [$basic, $premium] = $this->plans();
        $client = User::factory()->create(['role' => 'client']);
        $sub = $this->makeSubscription($client, $basic);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions/' . $sub->id . '/change-plan', [
            'membership_plan_id' => $premium->id,
            'simulate_decision' => 'declined',
        ], ['Authorization' => "Bearer $token"])
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'membership_plan_id' => $basic->id,
        ]);

        $this->assertDatabaseHas('payments', ['status' => 'failed']);
    }

    public function test_client_cannot_change_plan_of_another_user(): void
    {
        [$basic, $premium] = $this->plans();
        $owner = User::factory()->create(['role' => 'client']);
        $other = User::factory()->create(['role' => 'client']);
        $sub = $this->makeSubscription($owner, $basic);

        $token = $other->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions/' . $sub->id . '/change-plan', [
            'membership_plan_id' => $premium->id,
            'simulate_decision' => 'approved',
        ], ['Authorization' => "Bearer $token"])
            ->assertForbidden();

        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'membership_plan_id' => $basic->id,
        ]);
    }

    public function test_admin_can_change_plan_for_a_client_subscription(): void
    {
        [$basic, $premium] = $this->plans();
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $sub = $this->makeSubscription($client, $basic);

        $token = $admin->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/subscriptions/' . $sub->id . '/change-plan', [
            'membership_plan_id' => $premium->id,
            'simulate_decision' => 'approved',
        ], ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonPath('subscription.plan_id', $premium->id);
    }
}
