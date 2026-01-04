<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourierStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCourier() === true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    OrderStatus::IN_DELIVERY->value,
                    OrderStatus::DELIVERED->value,
                ]),
            ],
        ];
    }
}
