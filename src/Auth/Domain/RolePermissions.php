<?php

declare(strict_types=1);

namespace Auth\Domain;

/**
 * Mapping statique des permissions par rôle.
 *
 * Pas de table en BDD : KISS.
 */
final class RolePermissions
{
    /**
     * Permissions par rôle.
     *
     * @var array<string, Permission[]>
     */
    private const PERMISSIONS = [
        'super_admin' => [
            // Toutes les permissions + multi-tenant
            Permission::VIEW_BIKES,
            Permission::MANAGE_BIKES,
            Permission::DELETE_BIKES,
            Permission::MANAGE_CATEGORIES,
            Permission::MANAGE_RATES,
            Permission::VIEW_RENTALS,
            Permission::CREATE_RENTALS,
            Permission::CANCEL_RENTALS,
            Permission::VIEW_CUSTOMERS,
            Permission::MANAGE_CUSTOMERS,
            Permission::DELETE_CUSTOMERS,
            Permission::VIEW_MAINTENANCES,
            Permission::CREATE_MAINTENANCES,
            Permission::MANAGE_MAINTENANCES,
            Permission::CLOSE_MAINTENANCES,
            Permission::VIEW_STATS,
            Permission::MANAGE_USERS,
            // Permissions spécifiques Super Admin
            Permission::VIEW_TENANTS,
            Permission::MANAGE_TENANTS,
            Permission::DELETE_TENANTS,
            Permission::VIEW_SITES,
            Permission::MANAGE_SITES,
            Permission::DELETE_SITES,
        ],
        'admin' => [
            // Tout au niveau tenant
            Permission::VIEW_BIKES,
            Permission::MANAGE_BIKES,
            Permission::DELETE_BIKES,
            Permission::MANAGE_CATEGORIES,
            Permission::MANAGE_RATES,
            Permission::VIEW_RENTALS,
            Permission::CREATE_RENTALS,
            Permission::CANCEL_RENTALS,
            Permission::VIEW_CUSTOMERS,
            Permission::MANAGE_CUSTOMERS,
            Permission::DELETE_CUSTOMERS,
            Permission::VIEW_MAINTENANCES,
            Permission::CREATE_MAINTENANCES,
            Permission::MANAGE_MAINTENANCES,
            Permission::CLOSE_MAINTENANCES,
            Permission::VIEW_STATS,
            Permission::MANAGE_USERS,
            // Gestion des sites du tenant
            Permission::VIEW_SITES,
            Permission::MANAGE_SITES,
        ],
        'manager' => [
            Permission::VIEW_BIKES,
            Permission::MANAGE_BIKES,
            Permission::MANAGE_RATES,
            Permission::VIEW_RENTALS,
            Permission::CREATE_RENTALS,
            Permission::CANCEL_RENTALS,
            Permission::VIEW_CUSTOMERS,
            Permission::MANAGE_CUSTOMERS,
            Permission::VIEW_MAINTENANCES,
            Permission::CREATE_MAINTENANCES,
            Permission::MANAGE_MAINTENANCES,
            Permission::CLOSE_MAINTENANCES,
            Permission::VIEW_STATS,
            // Lecture seule des sites
            Permission::VIEW_SITES,
        ],
        'employee' => [
            Permission::VIEW_BIKES,
            Permission::VIEW_RENTALS,
            Permission::CREATE_RENTALS,
            Permission::VIEW_CUSTOMERS,
            Permission::MANAGE_CUSTOMERS,
            Permission::VIEW_MAINTENANCES,
            Permission::CREATE_MAINTENANCES,
            Permission::MANAGE_MAINTENANCES,
        ],
    ];

    /**
     * Retourne les permissions pour un rôle donné.
     *
     * @return Permission[]
     */
    public static function for(Role $role): array
    {
        return self::PERMISSIONS[$role->value];
    }

    /**
     * Vérifie si un rôle possède une permission.
     */
    public static function has(Role $role, Permission $permission): bool
    {
        return in_array($permission, self::for($role), true);
    }

    /**
     * Retourne toutes les permissions avec leur statut pour un rôle.
     *
     * @return array<string, bool>
     */
    public static function matrixFor(Role $role): array
    {
        $matrix = [];
        foreach (Permission::cases() as $permission) {
            $matrix[$permission->value] = self::has($role, $permission);
        }

        return $matrix;
    }
}
