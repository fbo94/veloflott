<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\CalculatePrice;

use Illuminate\Foundation\Http\FormRequest;

final class CalculatePriceRequest extends FormRequest
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
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'pricing_class_id' => ['required', 'uuid', 'exists:pricing_classes,id'],
            'duration_id' => ['required', 'uuid', 'exists:duration_definitions,id'],
            'custom_days' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'La catégorie est obligatoire.',
            'category_id.exists' => 'La catégorie n\'existe pas.',
            'pricing_class_id.required' => 'La classe tarifaire est obligatoire.',
            'pricing_class_id.exists' => 'La classe tarifaire n\'existe pas.',
            'duration_id.required' => 'La durée est obligatoire.',
            'duration_id.exists' => 'La durée n\'existe pas.',
            'custom_days.integer' => 'Le nombre de jours doit être un entier.',
            'custom_days.min' => 'Le nombre de jours doit être au moins 1.',
        ];
    }
}
