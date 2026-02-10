<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\CreateTenant;

use Illuminate\Foundation\Http\FormRequest;

final class CreateTenantRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'address' => ['required', 'string', 'max:500'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'subscription_plan_id' => ['required', 'string', 'uuid', 'exists:subscription_plans,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'organisation est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'slug.required' => 'Le slug est obligatoire.',
            'slug.regex' => 'Le slug doit contenir uniquement des lettres minuscules, chiffres et tirets.',
            'address.required' => 'L\'adresse est obligatoire.',
            'contact_email.required' => 'L\'email de contact est obligatoire.',
            'contact_email.email' => 'L\'email de contact doit être une adresse email valide.',
            'contact_phone.required' => 'Le numéro de téléphone est obligatoire.',
            'logo_url.url' => 'L\'URL du logo doit être une URL valide.',
            'subscription_plan_id.required' => 'Le plan d\'abonnement est obligatoire.',
            'subscription_plan_id.uuid' => 'L\'identifiant du plan doit être un UUID valide.',
            'subscription_plan_id.exists' => 'Le plan d\'abonnement sélectionné n\'existe pas.',
        ];
    }
}
