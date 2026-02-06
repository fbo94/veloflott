<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CalculatePrice;

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
            'category_id' => ['required', 'string', 'uuid'],
            'pricing_class_id' => ['required', 'string', 'uuid'],
            'duration_id' => ['required', 'string', 'uuid'],
            'custom_days' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'L\'identifiant de la catégorie est obligatoire.',
            'category_id.uuid' => 'L\'identifiant de la catégorie doit être un UUID valide.',
            'pricing_class_id.required' => 'L\'identifiant de la classe tarifaire est obligatoire.',
            'pricing_class_id.uuid' => 'L\'identifiant de la classe tarifaire doit être un UUID valide.',
            'duration_id.required' => 'L\'identifiant de la durée est obligatoire.',
            'duration_id.uuid' => 'L\'identifiant de la durée doit être un UUID valide.',
            'custom_days.integer' => 'Le nombre de jours doit être un nombre entier.',
            'custom_days.min' => 'Le nombre de jours doit être d\'au moins 1.',
        ];
    }
}
