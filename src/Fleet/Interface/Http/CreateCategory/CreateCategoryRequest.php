<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateCategory;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ];
    }
}
