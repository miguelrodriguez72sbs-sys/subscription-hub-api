<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';

    protected $description = 'Procesa las renovaciones de suscripciones vencidas y cobra el pago mensual simulado.';

    public function handle(PaymentService $paymentService): int
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where('next_billing_date', '<=', today())
            ->with('membershipPlan')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No hay suscripciones pendientes de renovacion.');

            return self::SUCCESS;
        }

        $renewed = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            $plan = $subscription->membershipPlan;

            if (! $plan) {
                continue;
            }

            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'status' => 'pending',
            ]);

            $payment = $paymentService->process($invoice);

            if ($payment->status === 'succeeded') {
                $subscription->update([
                    'ends_at' => now()->addDays($plan->duration_days),
                    'next_billing_date' => now()->addDays($plan->duration_days),
                ]);
                $renewed++;
                $this->info("Suscripcion #{$subscription->id} renovada.");
            } else {
                $subscription->update(['status' => 'expired']);
                $failed++;
                $this->warn("Suscripcion #{$subscription->id} marcada como expirada.");
            }
        }

        $this->info("Renovaciones procesadas: {$renewed} exitosas, {$failed} fallidas.");

        return self::SUCCESS;
    }
}
