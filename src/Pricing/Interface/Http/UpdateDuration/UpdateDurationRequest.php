<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\UpdateDuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateDurationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $durationId = $this->route('id');

        return [
            'code' => [
                'required',
                'string',
                'regex:/^[a-z0-9_]+$/',
                'max:50',
                Rule::unique('duration_definitions', 'code')
                    ->ignore($durationId)
                    ->whereNull('deleted_at'),
            ],
            'label' => 'required|string|max:100',
            'duration_hours' => 'nullable|integer|min:1',
            'duration_days' => 'nullable|integer|min:1',
            'is_custom' => 'sometimes|boolean',
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
            'duration_hours.min' => 'Le nombre d\'heures doit être au moins 1',
            'duration_days.min' => 'Le nombre de jours doit être au moins 1',
        ];
    }
}
