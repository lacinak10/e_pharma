<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id'           => ['required', 'integer', 'exists:categories,id'],
            'name'                  => ['required', 'string', 'max:255'],
            'reference'             => ['nullable', 'string', 'max:100', Rule::unique('medicines', 'reference')],
            'indication'            => ['nullable', 'string', 'max:255'],
            'dosage'                => ['nullable', 'string', 'max:60'],
            'pack'                  => ['nullable', 'string', 'max:60'],
            'description'           => ['nullable', 'string', 'max:2000'],
            'requires_prescription' => ['boolean'],
            // Prix indicatif en FCFA : confirmé par la pharmacie au moment de la vérification.
            'price'                 => ['required', 'integer', 'min:0'],
            'image'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'             => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id'           => 'catégorie',
            'name'                  => 'nom',
            'reference'             => 'référence',
            'indication'            => 'indication',
            'dosage'                => 'dosage',
            'pack'                  => 'conditionnement',
            'price'                 => 'prix indicatif',
            'requires_prescription' => 'ordonnance obligatoire',
        ];
    }
}
