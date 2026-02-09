<?php

declare(strict_types=1);

namespace Rental\Interface\Http\RentalSettings;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRentalSettingsRequest extends FormRequest
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
            'tenant_id' => ['nullable', 'uuid'],
            'site_id' => ['nullable', 'uuid'],
            'late_tolerance_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'hourly_late_rate' => ['required', 'numeric', 'min:0'],
            'daily_late_rate' => ['required', 'numeric', 'min:0'],
            'early_return_enabled' => ['required', 'boolean'],
            'early_return_fee_type' => ['required', 'string', 'in:percentage,fixed,none'],
            'early_return_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'early_return_fee_fixed' => ['nullable', 'numeric', 'min:0'],
            'max_rental_duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'min_reservation_hours_ahead' => ['required', 'integer', 'min:0', 'max:168'],
        ];
    }
}
