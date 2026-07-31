<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceService
{
    public function getAll()
    {
        return Invoice::with('subscription')->get();
    }

    public function getAllForUser(int $userId)
    {
        return Invoice::whereHas('subscription', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('subscription')->get();
    }

    public function find(int $id)
    {
        return Invoice::with('subscription')->findOrFail($id);
    }

    public function belongsToUser(Invoice $invoice, int $userId): bool
    {
        return $invoice->subscription->user_id === $userId;
    }

    public function updateStatus(int $id, string $status)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->status = $status;

        if ($status === 'paid' && ! $invoice->paid_at) {
            $invoice->paid_at = now();
        }

        $invoice->save();

        return $invoice;
    }
}
