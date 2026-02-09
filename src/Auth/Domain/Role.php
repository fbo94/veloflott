<?php

declare(strict_types=1);

namespace Auth\Domain;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';

    /**
     * Libellé français pour l'affichage.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::MANAGER => 'Manager',
            self::EMPLOYEE => 'Employé',
        };
    }

    /**
     * Vérifie si ce rôle a plus de privilèges qu'un autre.
     */
    public function isHigherThan(Role $other): bool
    {
        $hierarchy = [
            self::SUPER_ADMIN->value => 4,
            self::ADMIN->value => 3,
            self::MANAGER->value => 2,
            self::EMPLOYEE->value => 1,
        ];

        return $hierarchy[$this->value] > $hierarchy[$other->value];
    }

    /**
     * Tous les rôles disponibles.
     */
    public static function all(): array
    {
        return array_map(
            fn (Role $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            self::cases()
        );
    }
}
