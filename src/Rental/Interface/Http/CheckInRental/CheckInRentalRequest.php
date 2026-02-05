<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CheckInRental;

use Illuminate\Foundation\Http\FormRequest;

final class CheckInRentalRequest extends FormRequest
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
            'bikes_check_in' => ['required', 'array', 'min:1'],
            'bikes_check_in.*.bike_id' => ['required', 'string', 'uuid'],
            'bikes_check_in.*.client_height' => ['required', 'integer', 'min:100', 'max:250'],
            'bikes_check_in.*.client_weight' => ['required', 'integer', 'min:30', 'max:200'],
            'bikes_check_in.*.saddle_height' => ['required', 'integer', 'min:50', 'max:120'],
            'bikes_check_in.*.front_suspension_pressure' => ['nullable', 'integer', 'min:0', 'max:300'],
            'bikes_check_in.*.rear_suspension_pressure' => ['nullable', 'integer', 'min:0', 'max:300'],
            'bikes_check_in.*.pedal_type' => ['nullable', 'string', 'max:100'],
            'bikes_check_in.*.notes' => ['nullable', 'string', 'max:1000'],
            'customer_signature' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
