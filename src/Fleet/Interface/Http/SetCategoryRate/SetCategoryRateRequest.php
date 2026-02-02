<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\SetCategoryRate;

use Fleet\Domain\RateDuration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SetCategoryRateRequest extends FormRequest
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
            'duration' => ['required', 'string', Rule::in(array_column(RateDuration::cases(), 'value'))],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
