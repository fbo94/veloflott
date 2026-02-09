<?php

declare(strict_types=1);

namespace Rental\Interface\Http\EarlyReturn;

use Illuminate\Foundation\Http\FormRequest;

final class EarlyReturnRequest extends FormRequest
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
            'bikes_condition.*.bike_id' => ['required', 'uuid'],
            'bikes_condition.*.condition' => ['required', 'string', 'in:ok,minor_damage,major_damage'],
            'bikes_condition.*.damage_description' => ['nullable', 'string', 'max:1000'],
            'bikes_condition.*.damage_photos' => ['nullable', 'array'],
            'bikes_condition.*.damage_photos.*' => ['string'],
            'deposit_retained' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
