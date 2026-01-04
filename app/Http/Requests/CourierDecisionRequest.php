<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourierDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCourier() === true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
