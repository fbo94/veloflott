<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use Auth\Domain\Permission;
use Auth\Domain\Role;
use Auth\Domain\RolePermissions;

describe('RolePermissions', function () {
    describe('for', function () {
        it('returns all permissions for super_admin', function () {
            $permissions = RolePermissions::for(Role::SUPER_ADMIN);

            expect($permissions)->toBeArray();
            expect($permissions)->toContain(Permission::VIEW_BIKES);
            expect($permissions)->toContain(Permission::MANAGE_USERS);
            expect($permissions)->toContain(Permission::MANAGE_TENANTS);
            expect($permissions)->toContain(Permission::DELETE_TENANTS);
        });

        it('returns appropriate permissions for admin', function () {
            $permissions = RolePermissions::for(Role::ADMIN);

            expect($permissions)->toContain(Permission::VIEW_BIKES);
            expect($permissions)->toContain(Permission::MANAGE_USERS);
            expect($permissions)->toContain(Permission::MANAGE_SITES);
            expect($permissions)->not->toContain(Permission::MANAGE_TENANTS);
            expect($permissions)->not->toContain(Permission::DELETE_TENANTS);
        });

        it('returns appropriate permissions for manager', function () {
            $permissions = RolePermissions::for(Role::MANAGER);

            expect($permissions)->toContain(Permission::VIEW_BIKES);
            expect($permissions)->toContain(Permission::MANAGE_BIKES);
            expect($permissions)->toContain(Permission::VIEW_STATS);
            expect($permissions)->toContain(Permission::VIEW_SITES);
            expect($permissions)->not->toContain(Permission::MANAGE_USERS);
            expect($permissions)->not->toContain(Permission::MANAGE_SITES);
        });

        it('returns limited permissions for employee', function () {
            $permissions = RolePermissions::for(Role::EMPLOYEE);

            expect($permissions)->toContain(Permission::VIEW_BIKES);
            expect($permissions)->toContain(Permission::VIEW_RENTALS);
            expect($permissions)->toContain(Permission::CREATE_RENTALS);
            expect($permissions)->not->toContain(Permission::MANAGE_BIKES);
            expect($permissions)->not->toContain(Permission::VIEW_STATS);
            expect($permissions)->not->toContain(Permission::MANAGE_USERS);
        });
    });

    describe('has', function () {
        it('returns true when role has permission', function () {
            expect(RolePermissions::has(Role::SUPER_ADMIN, Permission::MANAGE_TENANTS))->toBeTrue();
            expect(RolePermissions::has(Role::ADMIN, Permission::MANAGE_USERS))->toBeTrue();
            expect(RolePermissions::has(Role::MANAGER, Permission::VIEW_STATS))->toBeTrue();
            expect(RolePermissions::has(Role::EMPLOYEE, Permission::VIEW_BIKES))->toBeTrue();
        });

        it('returns false when role does not have permission', function () {
            expect(RolePermissions::has(Role::ADMIN, Permission::MANAGE_TENANTS))->toBeFalse();
            expect(RolePermissions::has(Role::MANAGER, Permission::MANAGE_USERS))->toBeFalse();
            expect(RolePermissions::has(Role::EMPLOYEE, Permission::VIEW_STATS))->toBeFalse();
            expect(RolePermissions::has(Role::EMPLOYEE, Permission::MANAGE_BIKES))->toBeFalse();
        });
    });

    describe('matrixFor', function () {
        it('returns array with all permissions as keys', function () {
            $matrix = RolePermissions::matrixFor(Role::ADMIN);

            expect($matrix)->toBeArray();
            expect($matrix)->toHaveKey('view_bikes');
            expect($matrix)->toHaveKey('manage_users');
            expect($matrix)->toHaveKey('manage_tenants');
        });

        it('returns boolean values for each permission', function () {
            $matrix = RolePermissions::matrixFor(Role::MANAGER);

            foreach ($matrix as $permission => $hasPermission) {
                expect($hasPermission)->toBeBool();
            }
        });

        it('correctly reflects permissions for admin', function () {
            $matrix = RolePermissions::matrixFor(Role::ADMIN);

            expect($matrix['view_bikes'])->toBeTrue();
            expect($matrix['manage_users'])->toBeTrue();
            expect($matrix['manage_tenants'])->toBeFalse();
            expect($matrix['delete_tenants'])->toBeFalse();
        });

        it('correctly reflects permissions for employee', function () {
            $matrix = RolePermissions::matrixFor(Role::EMPLOYEE);

            expect($matrix['view_bikes'])->toBeTrue();
            expect($matrix['view_rentals'])->toBeTrue();
            expect($matrix['manage_bikes'])->toBeFalse();
            expect($matrix['view_stats'])->toBeFalse();
        });
    });

    describe('role hierarchy', function () {
        it('super_admin has more permissions than admin', function () {
            $superAdminPermissions = RolePermissions::for(Role::SUPER_ADMIN);
            $adminPermissions = RolePermissions::for(Role::ADMIN);

            expect(count($superAdminPermissions))->toBeGreaterThan(count($adminPermissions));
        });

        it('admin has more permissions than manager', function () {
            $adminPermissions = RolePermissions::for(Role::ADMIN);
            $managerPermissions = RolePermissions::for(Role::MANAGER);

            expect(count($adminPermissions))->toBeGreaterThan(count($managerPermissions));
        });

        it('manager has more permissions than employee', function () {
            $managerPermissions = RolePermissions::for(Role::MANAGER);
            $employeePermissions = RolePermissions::for(Role::EMPLOYEE);

            expect(count($managerPermissions))->toBeGreaterThan(count($employeePermissions));
        });
    });
});
