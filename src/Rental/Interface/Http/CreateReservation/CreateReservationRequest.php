<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CreateReservation;

use Illuminate\Foundation\Http\FormRequest;

final class CreateReservationRequest extends FormRequest
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
            'customer_id' => ['required', 'uuid'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'duration' => ['required', 'string', 'in:half_day,full_day,two_days,three_days,week,custom'],
            'custom_end_date' => ['nullable', 'date', 'after:start_date', 'required_if:duration,custom'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'bikes' => ['required', 'array', 'min:1'],
            'bikes.*.bike_id' => ['required', 'uuid'],
            'bikes.*.daily_rate' => ['required', 'numeric', 'min:0'],
            'bikes.*.quantity' => ['nullable', 'integer', 'min:1'],
            'equipments' => ['nullable', 'array'],
            'equipments.*.type' => ['required', 'string'],
            'equipments.*.quantity' => ['required', 'integer', 'min:1'],
            'equipments.*.price_per_unit' => ['required', 'numeric', 'min:0'],
        ];
    }
}
