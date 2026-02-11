<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\CompleteMaintenance;

use Illuminate\Foundation\Http\FormRequest;

final class CompleteMaintenanceRequest extends FormRequest
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
            'work_description' => ['nullable', 'string', 'max:2000'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'cost' => ['nullable', 'integer', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // S'assurer que photos est un tableau vide si non fourni
        if (!$this->has('photos')) {
            $this->merge(['photos' => []]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'work_description.max' => 'La description du travail ne peut pas dépasser 2000 caractères.',
            'parts_replaced.max' => 'La liste des pièces remplacées ne peut pas dépasser 1000 caractères.',
            'cost.integer' => 'Le coût doit être un nombre entier (en centimes).',
            'cost.min' => 'Le coût doit être supérieur ou égal à 0.',
        ];
    }
}
