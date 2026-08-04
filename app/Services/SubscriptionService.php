<?php 
namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\MembershipPlan;

class SubscriptionService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}
     
     public function getAll()
    {
        return Subscription::all();
    }

    public function getAllForUser(int $userId)
    {
        return Subscription::where('user_id', $userId)->get();
    }

   public function create(array $data)
{
    $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

    $startDate = now();

    $endDate = now()->addDays($plan->duration_days);

    $subscription = Subscription::create([
        'user_id' => $data['user_id'],
        'membership_plan_id' => $data['membership_plan_id'],
        'status' => 'active',
        'starts_at' => $startDate,
        'ends_at' => $endDate,
        'next_billing_date' => $endDate,
    ]);

    $invoice = Invoice::create([
        'subscription_id' => $subscription->id,
        'amount' => $plan->price,
        'status' => 'pending',
    ]);

    $this->paymentService->process($invoice);

    return $subscription;
}

    public function find(int $id)
    {
        return Subscription::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $plan = Subscription::findOrFail($id);

        $plan->update($data);

        return $plan;
    }

    public function delete(int $id)
    {
        $plan = Subscription::findOrFail($id);

        return $plan->delete();
    }
}