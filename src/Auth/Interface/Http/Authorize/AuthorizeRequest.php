<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Authorize;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation pour le callback d'autorisation.
 */
final class AuthorizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code d\'autorisation est requis.',
            'state.required' => 'Le state est requis pour la sécurité CSRF.',
        ];
    }
}
