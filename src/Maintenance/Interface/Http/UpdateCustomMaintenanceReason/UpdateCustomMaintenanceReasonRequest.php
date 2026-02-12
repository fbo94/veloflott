<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\UpdateCustomMaintenanceReason;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maintenance\Domain\MaintenanceCategory;

final class UpdateCustomMaintenanceReasonRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', Rule::in(array_column(MaintenanceCategory::cases(), 'value'))],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé est obligatoire.',
            'label.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'category.required' => 'La catégorie est obligatoire.',
            'category.in' => 'La catégorie doit être valide.',
            'is_active.required' => 'Le champ is_active est obligatoire.',
            'is_active.boolean' => 'Le champ is_active doit être un booléen.',
            'sort_order.required' => 'L\'ordre de tri est obligatoire.',
            'sort_order.integer' => 'L\'ordre de tri doit être un entier.',
            'sort_order.min' => 'L\'ordre de tri doit être positif ou nul.',
        ];
    }
}
