<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\CreateDiscountRule;

use Illuminate\Foundation\Http\FormRequest;

final class CreateDiscountRuleRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'nullable|uuid|exists:categories,id',
            'pricing_class_id' => 'nullable|uuid|exists:pricing_classes,id',
            'min_days' => 'nullable|integer|min:1',
            'min_duration_id' => 'nullable|uuid|exists:duration_definitions,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'label' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_cumulative' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.uuid' => 'L\'ID de catégorie doit être un UUID valide',
            'category_id.exists' => 'La catégorie n\'existe pas',
            'pricing_class_id.uuid' => 'L\'ID de classe tarifaire doit être un UUID valide',
            'pricing_class_id.exists' => 'La classe tarifaire n\'existe pas',
            'min_days.min' => 'Le nombre minimum de jours doit être au moins 1',
            'min_duration_id.uuid' => 'L\'ID de durée minimum doit être un UUID valide',
            'min_duration_id.exists' => 'La durée n\'existe pas',
            'discount_type.required' => 'Le type de réduction est requis',
            'discount_type.in' => 'Le type de réduction doit être "percentage" ou "fixed"',
            'discount_value.required' => 'La valeur de réduction est requise',
            'discount_value.min' => 'La valeur de réduction doit être supérieure à 0',
            'label.required' => 'Le libellé est requis',
        ];
    }
}
