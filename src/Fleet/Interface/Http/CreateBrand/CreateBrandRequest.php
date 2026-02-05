<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateBrand;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:brands,name'],
            'logo_url' => ['nullable', 'string', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la marque est obligatoire',
            'name.unique' => 'Cette marque existe déjà',
            'logo_url.url' => 'L\'URL du logo doit être valide',
        ];
    }
}
