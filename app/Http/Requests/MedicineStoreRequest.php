<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicineStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
