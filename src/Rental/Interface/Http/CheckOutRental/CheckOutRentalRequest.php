<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CheckOutRental;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Rental\Domain\BikeCondition;

final class CheckOutRentalRequest extends FormRequest
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
            'actual_return_date' => ['required', 'date'],
            'bikes_condition' => ['required', 'array', 'min:1'],
            'bikes_condition.*.bike_id' => ['required', 'string', 'uuid'],
            'bikes_condition.*.condition' => ['required', 'string', Rule::in(array_column(BikeCondition::cases(), 'value'))],
            'bikes_condition.*.damage_description' => ['nullable', 'string', 'max:1000'],
            'bikes_condition.*.damage_photos' => ['nullable', 'array'],
            'bikes_condition.*.damage_photos.*' => ['string', 'max:500'],
            'deposit_retained' => ['nullable', 'numeric', 'min:0'],
            'hourly_late_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'deposit_retained' => $this->input('deposit_retained', 0.0),
            'hourly_late_rate' => $this->input('hourly_late_rate', 10.0),
        ]);
    }
}
