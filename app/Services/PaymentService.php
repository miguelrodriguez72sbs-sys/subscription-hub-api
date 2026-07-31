<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class PaymentService
{
    public function process(Invoice $invoice): Payment
    {
        try {
            $result = config('payment.gateway') === 'stripe'
                ? $this->chargeWithStripe($invoice)
                : $this->simulate($invoice);
        } catch (\Throwable $e) {
            $result = [
                'success' => false,
                'gateway' => config('payment.gateway'),
                'reference' => null,
                'payload' => ['error' => $e->getMessage()],
            ];
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount,
            'status' => $result['success'] ? 'succeeded' : 'failed',
            'gateway' => $result['gateway'],
            'reference' => $result['reference'],
            'payload' => $result['payload'],
        ]);

        if ($result['success']) {
            $invoice->update([
                'status' => 'paid',
                'payment_reference' => $payment->reference,
                'paid_at' => now(),
            ]);
        } else {
            $invoice->update(['status' => 'failed']);
        }

        return $payment;
    }

    protected function chargeWithStripe(Invoice $invoice): array
    {
        $client = new Client;

        $response = $client->post(config('payment.stripe.url'), [
            'auth' => [config('payment.stripe.secret_key'), ''],
            'form_params' => [
                'amount' => (int) round($invoice->amount * 100),
                'currency' => config('payment.currency'),
                'source' => config('payment.stripe.test_card'),
                'description' => 'Renovacion de suscripcion #'.$invoice->subscription_id,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $success = ($response->getStatusCode() === 200)
            && isset($body['status'])
            && in_array($body['status'], ['succeeded', 'pending']);

        return [
            'success' => $success,
            'gateway' => 'stripe',
            'reference' => $body['id'] ?? null,
            'payload' => $body,
        ];
    }

    protected function simulate(Invoice $invoice): array
    {
        $shouldFail = mt_rand(1, 100) <= (config('payment.failure_rate') * 100);

        if ($shouldFail) {
            return [
                'success' => false,
                'gateway' => 'simulation',
                'reference' => null,
                'payload' => ['simulated' => true, 'reason' => 'Simulated payment failure.'],
            ];
        }

        return [
            'success' => true,
            'gateway' => 'simulation',
            'reference' => 'SIM-'.Str::upper(Str::random(10)),
            'payload' => ['simulated' => true],
        ];
    }
}
