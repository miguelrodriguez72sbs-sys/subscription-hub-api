<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_renews_due_subscription(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $plan = MembershipPlan::create(['name' => 'Pro', 'price' => 19.99, 'duration_days' => 30]);

        $subscription = Subscription::create([
            'user_id' => $client->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(10),
            'next_billing_date' => now()->subDays(10),
        ]);

        $this->artisan('subscriptions:process-renewals')
            ->expectsOutputToContain('Renovaciones procesadas')
            ->assertSuccessful();

        $subscription->refresh();

        $this->assertEquals('active', $subscription->status);
        $this->assertGreaterThanOrEqual(now()->addDays(29), $subscription->next_billing_date);

        $this->assertDatabaseHas('invoices', [
            'subscription_id' => $subscription->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', ['status' => 'succeeded']);
    }
}
