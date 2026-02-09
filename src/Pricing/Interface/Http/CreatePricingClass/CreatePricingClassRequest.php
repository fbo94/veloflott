<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\CreatePricingClass;

use Illuminate\Foundation\Http\FormRequest;

final class CreatePricingClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^[a-z0-9_]+$/', 'max:50', 'unique:pricing_classes,code'],
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.regex' => 'Le code ne peut contenir que des lettres minuscules, chiffres et underscores.',
            'code.unique' => 'Ce code existe déjà.',
            'label.required' => 'Le libellé est obligatoire.',
            'label.max' => 'Le libellé ne peut pas dépasser 100 caractères.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
            'color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB).',
            'sort_order.integer' => 'L\'ordre de tri doit être un nombre entier.',
            'sort_order.min' => 'L\'ordre de tri doit être positif.',
        ];
    }
}
