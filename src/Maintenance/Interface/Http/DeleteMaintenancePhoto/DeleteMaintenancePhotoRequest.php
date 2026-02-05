<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\DeleteMaintenancePhoto;

use Illuminate\Foundation\Http\FormRequest;

final class DeleteMaintenancePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo_url' => ['required', 'string', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo_url.required' => 'L\'URL de la photo est requise',
            'photo_url.url' => 'L\'URL de la photo doit être une URL valide',
        ];
    }
}
