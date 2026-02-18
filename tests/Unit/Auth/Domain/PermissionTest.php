<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use Auth\Domain\Permission;

describe('Permission', function () {
    describe('label', function () {
        it('returns correct label for fleet permissions', function () {
            expect(Permission::VIEW_BIKES->label())->toBe('Voir les vélos');
            expect(Permission::MANAGE_BIKES->label())->toBe('Gérer les vélos');
            expect(Permission::DELETE_BIKES->label())->toBe('Supprimer les vélos');
            expect(Permission::MANAGE_CATEGORIES->label())->toBe('Gérer les catégories');
            expect(Permission::MANAGE_RATES->label())->toBe('Gérer les tarifs');
        });

        it('returns correct label for rental permissions', function () {
            expect(Permission::VIEW_RENTALS->label())->toBe('Voir les locations');
            expect(Permission::CREATE_RENTALS->label())->toBe('Créer des locations');
            expect(Permission::CANCEL_RENTALS->label())->toBe('Annuler des locations');
        });

        it('returns correct label for customer permissions', function () {
            expect(Permission::VIEW_CUSTOMERS->label())->toBe('Voir les clients');
            expect(Permission::MANAGE_CUSTOMERS->label())->toBe('Gérer les clients');
            expect(Permission::DELETE_CUSTOMERS->label())->toBe('Supprimer les clients');
        });

        it('returns correct label for maintenance permissions', function () {
            expect(Permission::VIEW_MAINTENANCES->label())->toBe('Voir les maintenances');
            expect(Permission::CREATE_MAINTENANCES->label())->toBe('Créer des maintenances');
            expect(Permission::MANAGE_MAINTENANCES->label())->toBe('Gérer les maintenances');
            expect(Permission::CLOSE_MAINTENANCES->label())->toBe('Clôturer des maintenances');
        });

        it('returns correct label for dashboard permissions', function () {
            expect(Permission::VIEW_STATS->label())->toBe('Voir les statistiques');
        });

        it('returns correct label for user permissions', function () {
            expect(Permission::MANAGE_USERS->label())->toBe('Gérer les utilisateurs');
        });

        it('returns correct label for tenant permissions', function () {
            expect(Permission::VIEW_TENANTS->label())->toBe('Voir les tenants');
            expect(Permission::MANAGE_TENANTS->label())->toBe('Gérer les tenants');
            expect(Permission::DELETE_TENANTS->label())->toBe('Supprimer les tenants');
        });

        it('returns correct label for site permissions', function () {
            expect(Permission::VIEW_SITES->label())->toBe('Voir les sites');
            expect(Permission::MANAGE_SITES->label())->toBe('Gérer les sites');
            expect(Permission::DELETE_SITES->label())->toBe('Supprimer les sites');
        });
    });

    describe('module', function () {
        it('returns fleet for fleet permissions', function () {
            expect(Permission::VIEW_BIKES->module())->toBe('fleet');
            expect(Permission::MANAGE_BIKES->module())->toBe('fleet');
            expect(Permission::DELETE_BIKES->module())->toBe('fleet');
            expect(Permission::MANAGE_CATEGORIES->module())->toBe('fleet');
            expect(Permission::MANAGE_RATES->module())->toBe('fleet');
        });

        it('returns rental for rental permissions', function () {
            expect(Permission::VIEW_RENTALS->module())->toBe('rental');
            expect(Permission::CREATE_RENTALS->module())->toBe('rental');
            expect(Permission::CANCEL_RENTALS->module())->toBe('rental');
        });

        it('returns customer for customer permissions', function () {
            expect(Permission::VIEW_CUSTOMERS->module())->toBe('customer');
            expect(Permission::MANAGE_CUSTOMERS->module())->toBe('customer');
            expect(Permission::DELETE_CUSTOMERS->module())->toBe('customer');
        });

        it('returns maintenance for maintenance permissions', function () {
            expect(Permission::VIEW_MAINTENANCES->module())->toBe('maintenance');
            expect(Permission::CREATE_MAINTENANCES->module())->toBe('maintenance');
            expect(Permission::MANAGE_MAINTENANCES->module())->toBe('maintenance');
            expect(Permission::CLOSE_MAINTENANCES->module())->toBe('maintenance');
        });

        it('returns dashboard for dashboard permissions', function () {
            expect(Permission::VIEW_STATS->module())->toBe('dashboard');
        });

        it('returns auth for user permissions', function () {
            expect(Permission::MANAGE_USERS->module())->toBe('auth');
        });

        it('returns tenant for tenant permissions', function () {
            expect(Permission::VIEW_TENANTS->module())->toBe('tenant');
            expect(Permission::MANAGE_TENANTS->module())->toBe('tenant');
            expect(Permission::DELETE_TENANTS->module())->toBe('tenant');
        });

        it('returns site for site permissions', function () {
            expect(Permission::VIEW_SITES->module())->toBe('site');
            expect(Permission::MANAGE_SITES->module())->toBe('site');
            expect(Permission::DELETE_SITES->module())->toBe('site');
        });
    });

    describe('cases', function () {
        it('returns all permissions', function () {
            $cases = Permission::cases();

            expect($cases)->toBeArray();
            expect(count($cases))->toBeGreaterThanOrEqual(20);
        });
    });

    describe('from', function () {
        it('creates permission from valid string', function () {
            expect(Permission::from('view_bikes'))->toBe(Permission::VIEW_BIKES);
            expect(Permission::from('manage_users'))->toBe(Permission::MANAGE_USERS);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => Permission::from('invalid_permission'))->toThrow(\ValueError::class);
        });
    });

    describe('tryFrom', function () {
        it('returns permission for valid string', function () {
            expect(Permission::tryFrom('view_bikes'))->toBe(Permission::VIEW_BIKES);
        });

        it('returns null for invalid string', function () {
            expect(Permission::tryFrom('invalid'))->toBeNull();
        });
    });
});
