<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateDiscountRule;

use Fleet\Domain\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateDiscountRuleRequest extends FormRequest
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
            'category_id' => ['nullable', 'string', 'uuid'],
            'pricing_class_id' => ['nullable', 'string', 'uuid'],
            'min_days' => ['nullable', 'integer', 'min:1'],
            'min_duration_id' => ['nullable', 'string', 'uuid'],
            'discount_type' => ['required', 'string', Rule::in(array_column(DiscountType::cases(), 'value'))],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'label' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_cumulative' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.uuid' => 'L\'identifiant de la catégorie doit être un UUID valide.',
            'pricing_class_id.uuid' => 'L\'identifiant de la classe tarifaire doit être un UUID valide.',
            'min_days.integer' => 'Le nombre minimum de jours doit être un nombre entier.',
            'min_days.min' => 'Le nombre minimum de jours doit être d\'au moins 1.',
            'min_duration_id.uuid' => 'L\'identifiant de la durée minimale doit être un UUID valide.',
            'discount_type.required' => 'Le type de réduction est obligatoire.',
            'discount_type.in' => 'Le type de réduction doit être "percentage" ou "fixed".',
            'discount_value.required' => 'La valeur de la réduction est obligatoire.',
            'discount_value.numeric' => 'La valeur de la réduction doit être un nombre.',
            'discount_value.min' => 'La valeur de la réduction doit être supérieure à 0.',
            'label.required' => 'Le libellé est obligatoire.',
            'label.max' => 'Le libellé ne peut pas dépasser 200 caractères.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
            'is_cumulative.boolean' => 'Le cumul doit être vrai ou faux.',
            'priority.integer' => 'La priorité doit être un nombre entier.',
            'priority.min' => 'La priorité doit être positive.',
        ];
    }
}
