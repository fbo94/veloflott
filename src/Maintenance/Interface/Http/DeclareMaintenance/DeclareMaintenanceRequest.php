<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\DeclareMaintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceType;

final class DeclareMaintenanceRequest extends FormRequest
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
            'bike_id' => ['required', 'string', 'uuid'],
            'type' => ['required', 'string', Rule::in(array_column(MaintenanceType::cases(), 'value'))],
            'reason' => ['required', 'string', Rule::in(array_column(MaintenanceReason::cases(), 'value'))],
            'priority' => ['required', 'string', Rule::in(array_column(MaintenancePriority::cases(), 'value'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
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
            'bike_id.required' => 'L\'identifiant du vélo est obligatoire.',
            'bike_id.uuid' => 'L\'identifiant du vélo doit être un UUID valide.',
            'type.required' => 'Le type de maintenance est obligatoire.',
            'type.in' => 'Le type de maintenance doit être "preventive" ou "curative".',
            'reason.required' => 'La raison de la maintenance est obligatoire.',
            'reason.in' => 'La raison doit être valide (full_service, brake_bleeding, suspension, wheels, other).',
            'priority.required' => 'La priorité est obligatoire.',
            'priority.in' => 'La priorité doit être "normal" ou "urgent".',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            'scheduled_at.date' => 'La date de planification doit être une date valide.',
            'scheduled_at.date_format' => 'La date doit être au format Y-m-d H:i:s.',
        ];
    }
}
