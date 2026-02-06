<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateDuration;

use Illuminate\Foundation\Http\FormRequest;

final class CreateDurationRequest extends FormRequest
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
            'code' => ['required', 'string', 'regex:/^[a-z0-9_]+$/', 'max:50', 'unique:duration_definitions,code'],
            'label' => ['required', 'string', 'max:100'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'is_custom' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.regex' => 'Le code ne peut contenir que des lettres minuscules, chiffres et underscores.',
            'code.unique' => 'Ce code existe déjà.',
            'label.required' => 'Le libellé est obligatoire.',
            'label.max' => 'Le libellé ne peut pas dépasser 100 caractères.',
            'duration_hours.integer' => 'La durée en heures doit être un nombre entier.',
            'duration_hours.min' => 'La durée en heures doit être d\'au moins 1.',
            'duration_days.integer' => 'La durée en jours doit être un nombre entier.',
            'duration_days.min' => 'La durée en jours doit être d\'au moins 1.',
            'is_custom.boolean' => 'Le type personnalisé doit être vrai ou faux.',
            'sort_order.integer' => 'L\'ordre de tri doit être un nombre entier.',
            'sort_order.min' => 'L\'ordre de tri doit être positif.',
        ];
    }
}
