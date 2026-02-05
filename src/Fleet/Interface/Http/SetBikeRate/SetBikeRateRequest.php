<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\SetBikeRate;

use Illuminate\Foundation\Http\FormRequest;

final class SetBikeRateRequest extends FormRequest
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
            'bike_id' => ['required', 'uuid', 'exists:bikes,id'],
            'day_price' => ['required', 'numeric', 'min:0.01'],
            'half_day_price' => ['nullable', 'numeric', 'min:0.01'],
            'weekend_price' => ['nullable', 'numeric', 'min:0.01'],
            'week_price' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'bike_id.required' => 'L\'identifiant du vélo est requis.',
            'bike_id.uuid' => 'L\'identifiant du vélo doit être un UUID valide.',
            'bike_id.exists' => 'Le vélo spécifié n\'existe pas.',
            'day_price.required' => 'Le prix journalier est requis.',
            'day_price.numeric' => 'Le prix journalier doit être un nombre.',
            'day_price.min' => 'Le prix journalier doit être supérieur à 0.',
        ];
    }
}
