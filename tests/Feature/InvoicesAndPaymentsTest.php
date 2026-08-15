<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicesAndPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeClientWithSubscription(): array
    {
        $client = User::factory()->create(['role' => 'client']);
        $plan = MembershipPlan::create(['name' => 'Pro', 'price' => 19.99, 'duration_days' => 30]);

        $subscription = Subscription::create([
            'user_id' => $client->id,
            'membership_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
            'next_billing_date' => now()->addDays(25),
        ]);

        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'amount' => $plan->price,
            'status' => 'pending',
        ]);

        $token = $client->createToken('auth_token')->plainTextToken;

        return [$client, $invoice, $token];
    }

    public function test_client_can_list_own_invoices(): void
    {
        [, , $token] = $this->makeClientWithSubscription();

        $this->getJson('/api/invoices', ['Authorization' => "Bearer $token"])
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_client_can_pay_pending_invoice(): void
    {
        [, $invoice, $token] = $this->makeClientWithSubscription();

        $this->postJson('/api/payments', ['invoice_id' => $invoice->id], ['Authorization' => "Bearer $token"])
            ->assertCreated()
            ->assertJsonPath('data.status', 'succeeded');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_simulation_can_decline_payment(): void
    {
        [, $invoice, $token] = $this->makeClientWithSubscription();

        $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'simulate_decision' => 'declined',
        ], ['Authorization' => "Bearer $token"])
            ->assertCreated()
            ->assertJsonPath('data.status', 'failed');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'status' => 'failed',
            'gateway' => 'simulation',
        ]);
    }

    public function test_client_cannot_view_other_users_invoice(): void
    {
        [, $invoice, $token] = $this->makeClientWithSubscription();

        $this->getJson("/api/invoices/{$invoice->id}", ['Authorization' => "Bearer $token"])
            ->assertOk();
    }

    public function test_client_can_download_own_invoice_pdf(): void
    {
        [, $invoice, $token] = $this->makeClientWithSubscription();

        $response = $this->get("/api/invoices/{$invoice->id}/pdf", ['Authorization' => "Bearer $token"]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_admin_can_download_any_invoice_pdf(): void
    {
        [, $invoice] = $this->makeClientWithSubscription();

        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = $admin->createToken('auth_token')->plainTextToken;

        $this->get("/api/invoices/{$invoice->id}/pdf", ['Authorization' => "Bearer $adminToken"])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_update_invoice_status(): void
    {
        [$client, $invoice, $token] = $this->makeClientWithSubscription();

        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = $admin->createToken('auth_token')->plainTextToken;

        $this->patchJson("/api/invoices/{$invoice->id}/status", ['status' => 'paid'], ['Authorization' => "Bearer $adminToken"])
            ->assertOk()
            ->assertJsonPath('invoice.status', 'paid');
    }
}
