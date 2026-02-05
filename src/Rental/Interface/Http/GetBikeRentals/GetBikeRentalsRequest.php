<?php

declare(strict_types=1);

namespace Rental\Interface\Http\GetBikeRentals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GetBikeRentalsRequest extends FormRequest
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
            'filter' => ['sometimes', 'string', Rule::in(['all', 'past', 'current', 'upcoming'])],
        ];
    }

    public function messages(): array
    {
        return [
            'filter.in' => 'The filter must be one of: all, past, current, upcoming',
        ];
    }
}
