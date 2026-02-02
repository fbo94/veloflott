<?php

declare(strict_types=1);

namespace Auth\Interface\Http\UpdateUserRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation pour le changement de rôle.
 */
final class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['admin', 'manager', 'employee'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Le rôle est requis.',
            'role.in' => 'Le rôle doit être admin, manager ou employee.',
        ];
    }
}
