<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\BulkUpdatePricingRates;

use Illuminate\Foundation\Http\FormRequest;

final class BulkUpdatePricingRatesRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rates' => 'required|array|min:1',
            'rates.*.category_id' => 'required|uuid|exists:categories,id',
            'rates.*.pricing_class_id' => 'required|uuid|exists:pricing_classes,id',
            'rates.*.duration_id' => 'required|uuid|exists:duration_definitions,id',
            'rates.*.price' => 'required|numeric|min:0.01',
            'rates.*.is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rates.required' => 'Le tableau de tarifs est requis',
            'rates.array' => 'Le champ rates doit être un tableau',
            'rates.min' => 'Au moins un tarif doit être fourni',
            'rates.*.category_id.required' => 'L\'ID de catégorie est requis',
            'rates.*.category_id.uuid' => 'L\'ID de catégorie doit être un UUID valide',
            'rates.*.category_id.exists' => 'La catégorie n\'existe pas',
            'rates.*.pricing_class_id.required' => 'L\'ID de classe tarifaire est requis',
            'rates.*.pricing_class_id.uuid' => 'L\'ID de classe tarifaire doit être un UUID valide',
            'rates.*.pricing_class_id.exists' => 'La classe tarifaire n\'existe pas',
            'rates.*.duration_id.required' => 'L\'ID de durée est requis',
            'rates.*.duration_id.uuid' => 'L\'ID de durée doit être un UUID valide',
            'rates.*.duration_id.exists' => 'La durée n\'existe pas',
            'rates.*.price.required' => 'Le prix est requis',
            'rates.*.price.numeric' => 'Le prix doit être un nombre',
            'rates.*.price.min' => 'Le prix doit être supérieur à 0',
            'rates.*.is_active.boolean' => 'Le champ is_active doit être un booléen',
        ];
    }
}
