<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetPerformanceIndicators;

use Illuminate\Foundation\Http\FormRequest;

final class GetPerformanceIndicatorsRequest extends FormRequest
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
            'date_from' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_from.date' => 'La date de début doit être une date valide.',
            'date_from.date_format' => 'La date de début doit être au format Y-m-d.',
            'date_to.date' => 'La date de fin doit être une date valide.',
            'date_to.date_format' => 'La date de fin doit être au format Y-m-d.',
            'date_to.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
