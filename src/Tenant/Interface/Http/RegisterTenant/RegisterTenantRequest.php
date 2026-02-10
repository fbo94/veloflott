<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\RegisterTenant;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterTenantRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'subscription_plan_id' => ['required', 'string', 'uuid', 'exists:subscription_plans,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'owner_name.required' => 'Votre nom est obligatoire.',
            'owner_email.required' => 'Votre email est obligatoire.',
            'owner_email.email' => 'L\'email doit être une adresse email valide.',
            'organization_name.required' => 'Le nom de l\'organisation est obligatoire.',
            'subscription_plan_id.required' => 'Vous devez choisir un plan d\'abonnement.',
            'subscription_plan_id.exists' => 'Le plan d\'abonnement sélectionné n\'existe pas.',
        ];
    }

    public function authorize(): bool
    {
        // Endpoint public, pas besoin d'autorisation
        return true;
    }
}
