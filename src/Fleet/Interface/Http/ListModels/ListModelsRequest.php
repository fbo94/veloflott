<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListModels;

use Illuminate\Foundation\Http\FormRequest;

final class ListModelsRequest extends FormRequest
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
            'brand_id' => ['nullable', 'uuid', 'exists:brands,id'],
        ];
    }
}
