<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Keycloak;

use Auth\Domain\Role;
use Auth\Domain\User;
use Auth\Domain\UserRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Synchronise les utilisateurs depuis Keycloak vers la base locale.
 *
 * - Crée le user au premier login
 * - Met à jour les infos à chaque login
 */
final class UserSyncService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Synchronise un utilisateur depuis le payload JWT.
     *
     * @param object $tokenPayload Payload décodé du JWT Keycloak
     */
    public function sync(object $tokenPayload): User
    {
        $keycloakId = $tokenPayload->sub;

        // Chercher l'utilisateur existant
        $user = $this->users->findByKeycloakId($keycloakId);

        if ($user === null) {
            // Premier login : créer l'utilisateur
            $user = $this->createUser($tokenPayload);
        } else {
            // Login suivant : mettre à jour les infos
            $user = $this->updateUser($user, $tokenPayload);
        }

        // Mettre à jour la date de dernière connexion
        $user->recordLogin();

        $this->users->save($user);

        return $user;
    }

    private function createUser(object $tokenPayload): User
    {
        return new User(
            id: Str::uuid()->toString(),
            keycloakId: $tokenPayload->sub,
            email: $tokenPayload->email ?? '',
            firstName: $tokenPayload->given_name ?? null,
            lastName: $tokenPayload->family_name ?? null,
            role: Role::EMPLOYEE, // Rôle par défaut
            isActive: true,
        );
    }

    private function updateUser(User $user, object $tokenPayload): User
    {
        // Mettre à jour uniquement les infos provenant de Keycloak
        // Le rôle et is_active sont gérés côté Laravel
        return $user->updateFromKeycloak(
            email: $tokenPayload->email ?? $user->email(),
            firstName: $tokenPayload->given_name ?? $user->firstName(),
            lastName: $tokenPayload->family_name ?? $user->lastName(),
        );
    }
}
