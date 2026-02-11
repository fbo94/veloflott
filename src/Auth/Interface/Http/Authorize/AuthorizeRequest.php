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

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
            'code_verifier' => ['required', 'string', 'min:43', 'max:128'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code d\'autorisation est requis.',
            'state.required' => 'Le state est requis pour la sécurité CSRF.',
            'code_verifier.required' => 'Le code_verifier PKCE est requis.',
            'code_verifier.min' => 'Le code_verifier doit contenir au moins 43 caractères.',
            'code_verifier.max' => 'Le code_verifier ne peut pas dépasser 128 caractères.',
        ];
    }
}
