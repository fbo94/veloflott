<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\CreateCustomMaintenanceReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maintenance\Domain\MaintenanceCategory;

final class CreateCustomMaintenanceReasonRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:100'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', Rule::in(array_column(MaintenanceCategory::cases(), 'value'))],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Valeurs par défaut
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
        if (!$this->has('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.max' => 'Le code ne peut pas dépasser 100 caractères.',
            'label.required' => 'Le libellé est obligatoire.',
            'label.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'category.required' => 'La catégorie est obligatoire.',
            'category.in' => 'La catégorie doit être valide.',
            'is_active.boolean' => 'Le champ is_active doit être un booléen.',
            'sort_order.integer' => 'L\'ordre de tri doit être un entier.',
            'sort_order.min' => 'L\'ordre de tri doit être positif ou nul.',
        ];
    }
}
