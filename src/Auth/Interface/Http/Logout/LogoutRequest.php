<?php

declare(strict_types=1);

namespace Auth\Interface\Http\Logout;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Requête de validation pour le logout.
 */
final class LogoutRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'refresh_token.required' => 'Le refresh token est requis.',
            'refresh_token.string' => 'Le refresh token doit être une chaîne de caractères.',
        ];
    }
}
