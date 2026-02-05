<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CancelRental;

use Illuminate\Foundation\Http\FormRequest;

final class CancelRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'A cancellation reason is required',
            'cancellation_reason.min' => 'The cancellation reason must be at least 5 characters',
            'cancellation_reason.max' => 'The cancellation reason must not exceed 500 characters',
        ];
    }
}
