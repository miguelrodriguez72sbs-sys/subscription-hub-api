<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method' => ['nullable', Rule::in(['card', 'paypal', 'bank_transfer'])],
            'simulate_decision' => ['nullable', Rule::in(['approved', 'declined'])],
        ];
    }
}
