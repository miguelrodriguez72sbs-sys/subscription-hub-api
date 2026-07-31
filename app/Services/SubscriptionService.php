<?php 
namespace App\Services;

use App\Models\Subscription;
use App\Models\MembershipPlan;

class SubscriptionService
{
     public function getAll()
    {
        return Subscription::all();
    }

   public function create(array $data)
{
    $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

    $startDate = now();

    $endDate = now()->addDays($plan->duration_days);

    return Subscription::create([
        'user_id' => $data['user_id'],
        'membership_plan_id' => $data['membership_plan_id'],
        'status' => 'active',
        'starts_at' => $startDate,
        'ends_at' => $endDate,
        'next_billing_date' => $endDate,
    ]);
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