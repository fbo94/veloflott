<?php

declare(strict_types=1);

namespace Customer\Interface\Http\AnnotateCustomer;

use Illuminate\Foundation\Http\FormRequest;

final class AnnotateCustomerRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'annotation' => ['nullable', 'string', 'max:1000'],
            'is_risky_customer' => ['required', 'boolean'],
        ];
    }
}
