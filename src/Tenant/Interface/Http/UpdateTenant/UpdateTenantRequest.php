<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\UpdateTenant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTenantRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                "unique:tenants,slug,{$tenantId}",
            ],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'logo_url' => ['nullable', 'string', 'url', 'max:500'],
            'subscription_plan_id' => ['required', 'string', 'uuid', 'exists:subscription_plans,id'],
            'max_users' => ['required', 'integer', 'min:1', 'max:10000'],
            'max_bikes' => ['required', 'integer', 'min:1', 'max:100000'],
            'max_sites' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du tenant est requis.',
            'slug.required' => 'Le slug est requis.',
            'slug.regex' => 'Le slug ne peut contenir que des lettres minuscules, des chiffres et des tirets.',
            'slug.unique' => 'Ce slug est déjà utilisé par un autre tenant.',
            'contact_email.email' => 'L\'email de contact doit être une adresse email valide.',
            'subscription_plan_id.required' => 'Le plan d\'abonnement est requis.',
            'subscription_plan_id.exists' => 'Le plan d\'abonnement sélectionné n\'existe pas.',
            'max_users.required' => 'Le nombre maximum d\'utilisateurs est requis.',
            'max_users.min' => 'Le nombre maximum d\'utilisateurs doit être au moins 1.',
            'max_bikes.required' => 'Le nombre maximum de vélos est requis.',
            'max_bikes.min' => 'Le nombre maximum de vélos doit être au moins 1.',
            'max_sites.required' => 'Le nombre maximum de sites est requis.',
            'max_sites.min' => 'Le nombre maximum de sites doit être au moins 1.',
        ];
    }

    public function authorize(): bool
    {
        // Vérifier que l'utilisateur est super admin
        return $this->user()?->isSuperAdmin() ?? false;
    }
}
