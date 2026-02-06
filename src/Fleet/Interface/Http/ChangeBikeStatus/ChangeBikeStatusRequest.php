<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ChangeBikeStatus;

use Fleet\Domain\UnavailabilityReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ChangeBikeStatusRequest extends FormRequest
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
        $rules = [
            'status' => ['required', 'string', Rule::in(['available', 'maintenance', 'unavailable'])],
            'unavailability_reason' => ['nullable', 'string', Rule::in(array_column(UnavailabilityReason::cases(), 'value'))],
            'unavailability_comment' => ['nullable', 'string', 'max:1000'],
        ];

        // Make unavailability_reason required when status is unavailable
        if ($this->input('status') === 'unavailable') {
            $rules['unavailability_reason'][0] = 'required';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut doit être l\'une des valeurs suivantes : disponible, en maintenance, indisponible.',
            'unavailability_reason.required' => 'Le motif d\'indisponibilité est obligatoire lorsque le statut est "indisponible".',
            'unavailability_reason.in' => 'Le motif d\'indisponibilité doit être l\'une des valeurs suivantes : réservé, prêt, autre.',
            'unavailability_comment.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }
}
