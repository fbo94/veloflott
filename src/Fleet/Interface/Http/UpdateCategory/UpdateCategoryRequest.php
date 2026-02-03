<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateCategory;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCategoryRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
