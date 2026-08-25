<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class MedicineUpdateRequest extends MedicineStoreRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'reference' => [
                'nullable', 'string', 'max:100',
                Rule::unique('medicines', 'reference')->ignore($this->route('medicine')),
            ],
        ];
    }
}
