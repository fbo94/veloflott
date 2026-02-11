<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CreateRental;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Rental\Domain\EquipmentType;
use Rental\Domain\RentalDuration;

final class CreateRentalRequest extends FormRequest
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
            'customer_id' => ['required', 'string', 'uuid'],
            'start_date' => ['required', 'date'],
            'duration' => ['required', 'string', Rule::in(array_column(RentalDuration::cases(), 'value'))],
            'custom_end_date' => ['nullable', 'date', 'after:start_date'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],

            // Bikes
            'bikes' => ['required', 'array', 'min:1'],
            'bikes.*.bike_id' => ['required', 'string', 'uuid'],
            'bikes.*.daily_rate' => ['required', 'numeric', 'min:0'],
            'bikes.*.quantity' => ['nullable', 'integer', 'min:1'],

            // Equipments
            'equipments' => ['nullable', 'array'],
            'equipments.*.type' => ['required', 'string', Rule::in(array_column(EquipmentType::cases(), 'value'))],
            'equipments.*.quantity' => ['required', 'integer', 'min:1'],
            'equipments.*.price_per_unit' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('equipments')) {
            $this->merge(['equipments' => []]);
        }
    }
}
