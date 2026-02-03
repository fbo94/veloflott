<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetTodayActivity;

use Illuminate\Foundation\Http\FormRequest;

final class GetTodayActivityRequest extends FormRequest
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
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.date' => 'La date doit être une date valide.',
            'date.date_format' => 'La date doit être au format Y-m-d.',
        ];
    }
}
