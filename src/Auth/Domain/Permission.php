<?php

declare(strict_types=1);

namespace Auth\Domain;

enum Permission: string
{
    // ===== Fleet =====
    case VIEW_BIKES = 'view_bikes';
    case MANAGE_BIKES = 'manage_bikes';
    case DELETE_BIKES = 'delete_bikes';
    case MANAGE_CATEGORIES = 'manage_categories';
    case MANAGE_RATES = 'manage_rates';

    // ===== Rental =====
    case VIEW_RENTALS = 'view_rentals';
    case CREATE_RENTALS = 'create_rentals';
    case CANCEL_RENTALS = 'cancel_rentals';

    // ===== Customer =====
    case VIEW_CUSTOMERS = 'view_customers';
    case MANAGE_CUSTOMERS = 'manage_customers';
    case DELETE_CUSTOMERS = 'delete_customers';

    // ===== Maintenance =====
    case VIEW_MAINTENANCES = 'view_maintenances';
    case CREATE_MAINTENANCES = 'create_maintenances';
    case MANAGE_MAINTENANCES = 'manage_maintenances';
    case CLOSE_MAINTENANCES = 'close_maintenances';

    // ===== Dashboard =====
    case VIEW_STATS = 'view_stats';

    // ===== Users =====
    case MANAGE_USERS = 'manage_users';

    // ===== Tenants (Multi-tenant) =====
    case VIEW_TENANTS = 'view_tenants';
    case MANAGE_TENANTS = 'manage_tenants';
    case DELETE_TENANTS = 'delete_tenants';

    // ===== Sites (Multi-tenant) =====
    case VIEW_SITES = 'view_sites';
    case MANAGE_SITES = 'manage_sites';
    case DELETE_SITES = 'delete_sites';

    /**
     * Libellé français pour l'affichage.
     */
    public function label(): string
    {
        return match ($this) {
            self::VIEW_BIKES => 'Voir les vélos',
            self::MANAGE_BIKES => 'Gérer les vélos',
            self::DELETE_BIKES => 'Supprimer les vélos',
            self::MANAGE_CATEGORIES => 'Gérer les catégories',
            self::MANAGE_RATES => 'Gérer les tarifs',
            self::VIEW_RENTALS => 'Voir les locations',
            self::CREATE_RENTALS => 'Créer des locations',
            self::CANCEL_RENTALS => 'Annuler des locations',
            self::VIEW_CUSTOMERS => 'Voir les clients',
            self::MANAGE_CUSTOMERS => 'Gérer les clients',
            self::DELETE_CUSTOMERS => 'Supprimer les clients',
            self::VIEW_MAINTENANCES => 'Voir les maintenances',
            self::MANAGE_MAINTENANCES => 'Gérer les maintenances',
            self::CREATE_MAINTENANCES => 'Créer des maintenances',
            self::CLOSE_MAINTENANCES => 'Clôturer des maintenances',
            self::VIEW_STATS => 'Voir les statistiques',
            self::MANAGE_USERS => 'Gérer les utilisateurs',
            self::VIEW_TENANTS => 'Voir les tenants',
            self::MANAGE_TENANTS => 'Gérer les tenants',
            self::DELETE_TENANTS => 'Supprimer les tenants',
            self::VIEW_SITES => 'Voir les sites',
            self::MANAGE_SITES => 'Gérer les sites',
            self::DELETE_SITES => 'Supprimer les sites',
        };
    }

    /**
     * Module auquel appartient la permission.
     */
    public function module(): string
    {
        return match ($this) {
            self::VIEW_BIKES, self::MANAGE_BIKES, self::DELETE_BIKES,
            self::MANAGE_CATEGORIES, self::MANAGE_RATES => 'fleet',

            self::VIEW_RENTALS, self::CREATE_RENTALS,
            self::CANCEL_RENTALS => 'rental',

            self::VIEW_CUSTOMERS, self::MANAGE_CUSTOMERS,
            self::DELETE_CUSTOMERS => 'customer',

            self::VIEW_MAINTENANCES, self::CREATE_MAINTENANCES, self::MANAGE_MAINTENANCES,
            self::CLOSE_MAINTENANCES => 'maintenance',

            self::VIEW_STATS => 'dashboard',

            self::MANAGE_USERS => 'auth',

            self::VIEW_TENANTS, self::MANAGE_TENANTS,
            self::DELETE_TENANTS => 'tenant',

            self::VIEW_SITES, self::MANAGE_SITES,
            self::DELETE_SITES => 'site',
        };
    }
}
