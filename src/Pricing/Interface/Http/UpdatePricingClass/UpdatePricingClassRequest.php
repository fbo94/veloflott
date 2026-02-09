<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\UpdatePricingClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePricingClassRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pricingClassId = $this->route('id');

        return [
            'code' => [
                'required',
                'string',
                'regex:/^[a-z0-9_]+$/',
                'max:50',
                Rule::unique('pricing_classes', 'code')
                    ->ignore($pricingClassId)
                    ->whereNull('deleted_at'),
            ],
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code est requis',
            'code.regex' => 'Le code ne peut contenir que des lettres minuscules, chiffres et underscores',
            'code.unique' => 'Ce code est déjà utilisé',
            'label.required' => 'Le libellé est requis',
            'label.max' => 'Le libellé ne peut dépasser 100 caractères',
            'color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB)',
        ];
    }
}
