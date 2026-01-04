<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isClient() === true;
    }

    public function rules(): array
    {
        return [
            'delivery_address' => ['required', 'string', 'max:255'],
            'delivery_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
