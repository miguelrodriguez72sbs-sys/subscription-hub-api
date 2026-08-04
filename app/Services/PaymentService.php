<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Procesa el pago de una factura.
     *
     * En modo "stripe" envia un cargo real al Sandbox de Stripe (tarjeta de prueba).
     * En modo "simulation" simula la pasarela: acepta ($decision = 'approved')
     * o rechaza ($decision = 'declined') el cobro. Sin decision, aplica
     * PAYMENT_FAILURE_RATE como probabilidad de rechazo.
     */
    public function process(Invoice $invoice, ?string $decision = null): Payment
    {
        try {
            $result = config('payment.gateway') === 'stripe'
                ? $this->chargeWithStripe($invoice)
                : $this->simulate($invoice, $decision);
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
        $client = new Client();

        $response = $client->post(config('payment.stripe.url'), [
            'auth' => [config('payment.stripe.secret_key'), ''],
            'form_params' => [
                'amount' => (int) round($invoice->amount * 100),
                'currency' => config('payment.currency'),
                'source' => config('payment.stripe.test_card'),
                'description' => 'Renovacion de suscripcion #' . $invoice->subscription_id,
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

    /**
     * Simula una pasarela de pagos y decide si el cobro se procesa o se rechaza.
     */
    protected function simulate(Invoice $invoice, ?string $decision = null): array
    {
        $approved = match ($decision) {
            'approved' => true,
            'declined' => false,
            default => mt_rand(1, 100) > (config('payment.failure_rate') * 100),
        };

        $payload = $approved
            ? [
                'id' => 'sim_' . Str::lower(Str::random(24)),
                'object' => 'charge',
                'amount' => (int) round($invoice->amount * 100),
                'currency' => config('payment.currency'),
                'status' => 'succeeded',
                'paid' => true,
                'simulated' => true,
                'outcome' => [
                    'type' => 'authorized',
                    'network_status' => 'approved_by_network',
                ],
            ]
            : [
                'id' => 'sim_' . Str::lower(Str::random(24)),
                'object' => 'charge',
                'amount' => (int) round($invoice->amount * 100),
                'currency' => config('payment.currency'),
                'status' => 'failed',
                'paid' => false,
                'simulated' => true,
                'outcome' => [
                    'type' => 'issuer_declined',
                    'reason' => 'generic_decline',
                ],
            ];

        return [
            'success' => $approved,
            'gateway' => 'simulation',
            'reference' => $approved ? 'SIM-' . Str::upper(Str::random(10)) : null,
            'payload' => $payload,
        ];
    }
}
