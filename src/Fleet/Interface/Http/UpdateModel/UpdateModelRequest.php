<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateModel;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'brand_id' => ['required', 'string', 'uuid', 'exists:brands,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du modèle est obligatoire',
            'brand_id.required' => 'La marque est obligatoire',
            'brand_id.exists' => 'Cette marque n\'existe pas',
        ];
    }
}
