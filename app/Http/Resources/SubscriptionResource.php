<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
           return [
    'id' => $this->id,
    'user_id' => $this->user_id,
    'application' => $this->membershipPlan->application,
    'plan' => $this->membershipPlan->name,
    'status' => $this->status,
    'starts_at' => $this->starts_at,
    'ends_at' => $this->ends_at,
    'next_billing_date' => $this->next_billing_date,
];
        
    }
}
