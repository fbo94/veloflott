<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\ListMaintenances;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceStatus;

final class ListMaintenancesRequest extends FormRequest
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
            'bike_id' => ['nullable', 'string', 'uuid'],
            'status' => ['nullable', 'string', Rule::in(array_column(MaintenanceStatus::cases(), 'value'))],
            'priority' => ['nullable', 'string', Rule::in(array_column(MaintenancePriority::cases(), 'value'))],
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
            'bike_id.uuid' => 'L\'identifiant du vélo doit être un UUID valide.',
            'status.in' => 'Le statut doit être valide (todo, in_progress, completed).',
            'priority.in' => 'La priorité doit être valide (normal, urgent).',
            'date_from.date' => 'La date de début doit être une date valide.',
            'date_from.date_format' => 'La date de début doit être au format Y-m-d.',
            'date_to.date' => 'La date de fin doit être une date valide.',
            'date_to.date_format' => 'La date de fin doit être au format Y-m-d.',
            'date_to.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ];
    }
}
