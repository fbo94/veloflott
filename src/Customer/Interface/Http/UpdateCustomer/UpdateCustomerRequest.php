<?php

declare(strict_types=1);

namespace Customer\Interface\Http\UpdateCustomer;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCustomerRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'identity_document_type' => ['nullable', 'string', 'max:50'],
            'identity_document_number' => ['nullable', 'string', 'max:100'],
            'height' => ['nullable', 'integer', 'min:100', 'max:250'],
            'weight' => ['nullable', 'integer', 'min:30', 'max:200'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('photos')) {
            $this->merge(['photos' => []]);
        }
    }
}
