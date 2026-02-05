<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\UploadMaintenancePhoto;

use Illuminate\Foundation\Http\FormRequest;

final class UploadMaintenancePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'max:5120'], // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Une photo est requise',
            'photo.image' => 'Le fichier doit être une image',
            'photo.max' => 'La photo ne doit pas dépasser 5MB',
        ];
    }
}
