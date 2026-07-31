<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function adminToken(): string
    {
        return User::factory()->create(['role' => 'admin'])->createToken('auth_token')->plainTextToken;
    }

    public function test_admin_dashboard_returns_stats(): void
    {
        $this->getJson('/api/dashboard', ['Authorization' => 'Bearer '.$this->adminToken()])
            ->assertOk()
            ->assertJsonStructure([
                'total_customers', 'total_plans', 'active_subscriptions',
                'total_revenue', 'revenue_last_30_days',
            ]);
    }

    public function test_client_dashboard_returns_stats(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $plan = MembershipPlan::create(['name' => 'Basico', 'price' => 9.99, 'duration_days' => 30]);

        Subscription::create([
            'user_id' => $client->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'next_billing_date' => now()->addDays(30),
        ]);

        $token = $client->createToken('auth_token')->plainTextToken;

        $this->getJson('/api/dashboard', ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonStructure(['active_subscription', 'total_subscriptions', 'next_billing_date']);
    }

    public function test_admin_reports_endpoints(): void
    {
        $token = $this->adminToken();

        $this->getJson('/api/reports', ['Authorization' => "Bearer $token"])->assertOk();
        $this->getJson('/api/reports/revenue', ['Authorization' => "Bearer $token"])->assertOk();
        $this->getJson('/api/reports/subscriptions', ['Authorization' => "Bearer $token"])->assertOk();
        $this->getJson('/api/reports/invoices', ['Authorization' => "Bearer $token"])->assertOk();
    }

    public function test_client_cannot_access_reports(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $token = $client->createToken('auth_token')->plainTextToken;

        $this->getJson('/api/reports', ['Authorization' => "Bearer $token"])->assertStatus(403);
    }
}
