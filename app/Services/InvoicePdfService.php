<?php

namespace App\Services;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfService
{
    public function render(Invoice $invoice): string
    {
        $invoice->loadMissing([
            'subscription.user',
            'subscription.membershipPlan',
            'payments',
        ]);

        $payment = $invoice->status === 'paid'
            ? $invoice->payments->where('status', 'succeeded')->sortByDesc('created_at')->first()
            : $invoice->payments->sortByDesc('created_at')->first();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('isJavascriptEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml(view('pdf.invoice', [
            'invoice' => $invoice,
            'subscription' => $invoice->subscription,
            'plan' => $invoice->subscription->membershipPlan,
            'customer' => $invoice->subscription->user,
            'payment' => $payment,
            'currency' => strtoupper(config('payment.currency', 'usd')),
        ])->render(), 'UTF-8');
        $dompdf->render();

        return $dompdf->output();
    }
}
