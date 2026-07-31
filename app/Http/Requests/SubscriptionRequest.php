<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
    'user_id' => 'required|exists:users,id',
    'membership_plan_id' => 'required|exists:membership_plans,id'
];
    }
}
