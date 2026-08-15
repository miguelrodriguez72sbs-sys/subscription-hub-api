<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'application' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'status' => 'boolean'
        ];
    }
}