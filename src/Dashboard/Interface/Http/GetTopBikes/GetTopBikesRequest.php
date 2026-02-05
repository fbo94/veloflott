<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetTopBikes;

use Illuminate\Foundation\Http\FormRequest;

final class GetTopBikesRequest extends FormRequest
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
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'limit.integer' => 'La limite doit être un nombre entier.',
            'limit.min' => 'La limite doit être au minimum 1.',
            'limit.max' => 'La limite doit être au maximum 50.',
        ];
    }
}
