<?php

declare(strict_types=1);

namespace Auth\Interface\Http\GetAuthorizationUrl;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation pour la requête d'URL d'autorisation avec PKCE.
 */
final class GetAuthorizationUrlRequest extends FormRequest
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
            'code_challenge' => ['required', 'string', 'min:43', 'max:128'],
            'code_challenge_method' => ['sometimes', 'string', 'in:S256,plain'],
            'redirect_url' => ['sometimes', 'nullable', 'string', 'url'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code_challenge.required' => 'Le code_challenge PKCE est requis.',
            'code_challenge.min' => 'Le code_challenge doit contenir au moins 43 caractères.',
            'code_challenge.max' => 'Le code_challenge ne peut pas dépasser 128 caractères.',
            'code_challenge_method.in' => 'La méthode de challenge doit être S256 ou plain.',
        ];
    }
}
