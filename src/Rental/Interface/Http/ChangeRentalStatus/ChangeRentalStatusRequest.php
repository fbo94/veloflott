<?php

declare(strict_types=1);

namespace Rental\Interface\Http\ChangeRentalStatus;

use Illuminate\Foundation\Http\FormRequest;
use Rental\Domain\RentalStatus;

final class ChangeRentalStatusRequest extends FormRequest
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
        $validStatuses = array_map(
            fn (RentalStatus $status) => $status->value,
            RentalStatus::cases(),
        );

        return [
            'status' => ['required', 'string', 'in:' . implode(',', $validStatuses)],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'The new status is required.',
            'status.in' => 'The status must be one of: reserved, pending, active, completed, cancelled.',
        ];
    }
}
