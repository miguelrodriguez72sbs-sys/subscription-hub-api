<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'gateway' => $this->gateway,
            'reference' => $this->reference,
            'created_at' => $this->created_at,
        ];
    }
}
