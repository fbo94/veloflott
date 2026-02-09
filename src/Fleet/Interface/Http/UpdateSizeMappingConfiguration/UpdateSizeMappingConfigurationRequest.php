<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateSizeMappingConfiguration;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSizeMappingConfigurationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sizes' => ['required', 'array', 'size:6'],
            'sizes.*.letter' => ['required', 'string', 'in:xs,s,m,l,xl,xxl'],
            'sizes.*.label' => ['required', 'string'],
            'sizes.*.cm' => ['required', 'array:min,max'],
            'sizes.*.cm.min' => ['required', 'integer', 'min:0'],
            'sizes.*.cm.max' => ['required', 'integer', 'min:0', 'gte:sizes.*.cm.min'],
            'sizes.*.inch' => ['required', 'array:min,max'],
            'sizes.*.inch.min' => ['required', 'integer', 'min:0'],
            'sizes.*.inch.max' => ['required', 'integer', 'min:0', 'gte:sizes.*.inch.min'],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sizes.required' => 'Les tailles sont requises',
            'sizes.size' => 'Vous devez fournir exactement 6 tailles (XS, S, M, L, XL, XXL)',
            'sizes.*.letter.in' => 'La lettre de la taille doit être : xs, s, m, l, xl ou xxl',
            'sizes.*.cm.min.required' => 'La valeur minimale en cm est requise',
            'sizes.*.cm.max.required' => 'La valeur maximale en cm est requise',
            'sizes.*.cm.max.gte' => 'La valeur maximale doit être supérieure ou égale à la valeur minimale',
            'sizes.*.inch.min.required' => 'La valeur minimale en pouces est requise',
            'sizes.*.inch.max.required' => 'La valeur maximale en pouces est requise',
            'sizes.*.inch.max.gte' => 'La valeur maximale doit être supérieure ou égale à la valeur minimale',
        ];
    }
}
