<?php 
namespace App\Services;

use App\Http\Resources\PaymentResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\MembershipPlan;
use Illuminate\Validation\ValidationException;

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

    public function changePlan(Subscription $subscription, array $data): array
    {
        $currentPlan = $subscription->membershipPlan;

        $targetPlan = MembershipPlan::findOrFail($data['membership_plan_id']);

        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages([
                'subscription' => 'Solo puedes cambiar el plan de una suscripcion activa.',
            ]);
        }

        if ($targetPlan->id === $currentPlan->id) {
            throw ValidationException::withMessages([
                'membership_plan_id' => 'Ya tienes este plan activo.',
            ]);
        }

        if ($currentPlan->application !== $targetPlan->application) {
            throw ValidationException::withMessages([
                'membership_plan_id' => 'El nuevo plan debe pertenecer a la misma aplicacion. Cancela esta suscripcion y suscribete a la otra aplicacion.',
            ]);
        }

        $difference = round($targetPlan->price - $currentPlan->price, 2);

        $payment = null;

        if ($difference > 0) {
            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'amount' => $difference,
                'status' => 'pending',
            ]);

            $payment = $this->paymentService->process($invoice, $data['simulate_decision'] ?? null);

            if ($payment->status !== 'succeeded') {
                throw ValidationException::withMessages([
                    'payment' => 'El cobro de la diferencia fue rechazado. El plan no fue modificado.',
                ]);
            }
        }

        $subscription->update(['membership_plan_id' => $targetPlan->id]);

        $subscription->refresh();

        $subscription->load('membershipPlan');

        if ($payment) {
            $payment->update(['payment_method' => $data['payment_method'] ?? null]);
        }

        return [
            'message' => $difference > 0
                ? 'Plan actualizado correctamente. Se cobro la diferencia de ' . number_format($difference, 2) . '.'
                : 'Plan actualizado correctamente.',
            'subscription' => new SubscriptionResource($subscription),
            'amount_charged' => $difference,
            'payment_method' => $data['payment_method'] ?? null,
            'payment' => $payment ? new PaymentResource($payment) : null,
        ];
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