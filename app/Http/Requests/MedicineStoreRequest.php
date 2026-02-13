<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // si tu as un middleware EnsureRole manager, tu peux laisser true
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required','integer','exists:categories,id'],
            'name' => ['required','string','max:255'],

            // reference unique (SKU)
            'reference' => ['required','string','max:100', Rule::unique('medicines','reference')],

            'description' => ['nullable','string','max:2000'],

            // Prix en FCFA (int)
            'price' => ['required','integer','min:0'],

            // Stock et seuil
            'stock' => ['required','integer','min:0'],
            'alert_threshold' => ['required','integer','min:0'],

            // Image (optionnelle)
            'image_url' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            // (optionnel) activer/désactiver
            'is_active' => ['nullable','boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La catégorie est obligatoire.',
            'category_id.exists' => 'La catégorie sélectionnée est invalide.',

            'name.required' => 'Le nom du produit est obligatoire.',

            'reference.required' => 'La référence (SKU) est obligatoire.',
            'reference.unique' => 'Cette référence existe déjà.',

            'price.required' => 'Le prix est obligatoire.',
            'price.integer' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',

            'stock.required' => 'Le stock est obligatoire.',
            'stock.integer' => 'Le stock doit être un nombre.',
            'stock.min' => 'Le stock ne peut pas être négatif.',

            'alert_threshold.required' => "Le seuil d'alerte est obligatoire.",
            'alert_threshold.integer' => "Le seuil d'alerte doit être un nombre.",
            'alert_threshold.min' => "Le seuil d'alerte ne peut pas être négatif.",

            'image_url.image' => 'Le fichier doit être une image.',
            'image_url.mimes' => 'Formats acceptés : jpg, jpeg, png, webp.',
            'image_url.max' => 'Image trop grande (max 2MB).',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisation simple
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'reference' => is_string($this->reference) ? trim($this->reference) : $this->reference,
            'is_active' => (bool)($this->is_active ?? true),
        ]);
    }
}
