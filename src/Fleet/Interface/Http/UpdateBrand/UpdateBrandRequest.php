<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateBrand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('brands', 'name')->ignore($brandId)],
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
