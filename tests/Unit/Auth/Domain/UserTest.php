<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use Auth\Domain\Permission;
use Auth\Domain\Role;
use Auth\Domain\User;

describe('User', function () {
    function createUser(array $overrides = []): User
    {
        return new User(
            id: array_key_exists('id', $overrides) ? $overrides['id'] : 'user-123',
            keycloakId: array_key_exists('keycloakId', $overrides) ? $overrides['keycloakId'] : 'keycloak-456',
            tenantId: array_key_exists('tenantId', $overrides) ? $overrides['tenantId'] : 'tenant-789',
            email: array_key_exists('email', $overrides) ? $overrides['email'] : 'john.doe@example.com',
            firstName: array_key_exists('firstName', $overrides) ? $overrides['firstName'] : 'John',
            lastName: array_key_exists('lastName', $overrides) ? $overrides['lastName'] : 'Doe',
            role: array_key_exists('role', $overrides) ? $overrides['role'] : Role::MANAGER,
            isActive: array_key_exists('isActive', $overrides) ? $overrides['isActive'] : true,
            lastLoginAt: array_key_exists('lastLoginAt', $overrides) ? $overrides['lastLoginAt'] : null,
            createdAt: array_key_exists('createdAt', $overrides) ? $overrides['createdAt'] : null,
            updatedAt: array_key_exists('updatedAt', $overrides) ? $overrides['updatedAt'] : null,
        );
    }

    describe('construction', function () {
        it('creates user with all properties', function () {
            $user = createUser();

            expect($user->id())->toBe('user-123');
            expect($user->keycloakId())->toBe('keycloak-456');
            expect($user->tenantId())->toBe('tenant-789');
            expect($user->email())->toBe('john.doe@example.com');
            expect($user->firstName())->toBe('John');
            expect($user->lastName())->toBe('Doe');
            expect($user->role())->toBe(Role::MANAGER);
            expect($user->isActive())->toBeTrue();
        });

        it('creates user with null tenant id', function () {
            $user = createUser(['tenantId' => null]);

            expect($user->tenantId())->toBeNull();
        });

        it('sets default timestamps', function () {
            $user = createUser();

            expect($user->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
            expect($user->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
            expect($user->lastLoginAt())->toBeNull();
        });

        it('accepts custom timestamps', function () {
            $createdAt = new \DateTimeImmutable('2024-01-01');
            $updatedAt = new \DateTimeImmutable('2024-01-02');
            $lastLoginAt = new \DateTimeImmutable('2024-01-03');

            $user = createUser([
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
                'lastLoginAt' => $lastLoginAt,
            ]);

            expect($user->createdAt())->toBe($createdAt);
            expect($user->updatedAt())->toBe($updatedAt);
            expect($user->lastLoginAt())->toBe($lastLoginAt);
        });
    });

    describe('fullName', function () {
        it('returns concatenated first and last name', function () {
            $user = createUser(['firstName' => 'Jane', 'lastName' => 'Smith']);

            expect($user->fullName())->toBe('Jane Smith');
        });

        it('returns email when names are null', function () {
            $user = createUser(['firstName' => null, 'lastName' => null, 'email' => 'test@example.com']);

            expect($user->fullName())->toBe('test@example.com');
        });

        it('returns email when names are empty', function () {
            $user = createUser(['firstName' => '', 'lastName' => '', 'email' => 'test@example.com']);

            expect($user->fullName())->toBe('test@example.com');
        });

        it('handles only first name', function () {
            $user = createUser(['firstName' => 'John', 'lastName' => null]);

            expect($user->fullName())->toBe('John');
        });

        it('handles only last name', function () {
            $user = createUser(['firstName' => null, 'lastName' => 'Doe']);

            expect($user->fullName())->toBe('Doe');
        });
    });

    describe('hasPermission', function () {
        it('returns true for permissions the role has', function () {
            $manager = createUser(['role' => Role::MANAGER]);

            expect($manager->hasPermission(Permission::VIEW_BIKES))->toBeTrue();
            expect($manager->hasPermission(Permission::MANAGE_BIKES))->toBeTrue();
            expect($manager->hasPermission(Permission::VIEW_STATS))->toBeTrue();
        });

        it('returns false for permissions the role does not have', function () {
            $manager = createUser(['role' => Role::MANAGER]);

            expect($manager->hasPermission(Permission::MANAGE_USERS))->toBeFalse();
            expect($manager->hasPermission(Permission::MANAGE_TENANTS))->toBeFalse();
        });

        it('super admin has all permissions', function () {
            $superAdmin = createUser(['role' => Role::SUPER_ADMIN]);

            expect($superAdmin->hasPermission(Permission::MANAGE_TENANTS))->toBeTrue();
            expect($superAdmin->hasPermission(Permission::MANAGE_USERS))->toBeTrue();
            expect($superAdmin->hasPermission(Permission::DELETE_CUSTOMERS))->toBeTrue();
        });
    });

    describe('hasRole', function () {
        it('returns true for matching role', function () {
            $admin = createUser(['role' => Role::ADMIN]);

            expect($admin->hasRole(Role::ADMIN))->toBeTrue();
        });

        it('returns false for non-matching role', function () {
            $admin = createUser(['role' => Role::ADMIN]);

            expect($admin->hasRole(Role::SUPER_ADMIN))->toBeFalse();
            expect($admin->hasRole(Role::MANAGER))->toBeFalse();
        });
    });

    describe('isAdmin', function () {
        it('returns true for admin role', function () {
            $admin = createUser(['role' => Role::ADMIN]);

            expect($admin->isAdmin())->toBeTrue();
        });

        it('returns false for other roles', function () {
            $superAdmin = createUser(['role' => Role::SUPER_ADMIN]);
            $manager = createUser(['role' => Role::MANAGER]);

            expect($superAdmin->isAdmin())->toBeFalse();
            expect($manager->isAdmin())->toBeFalse();
        });
    });

    describe('isSuperAdmin', function () {
        it('returns true for super admin role', function () {
            $superAdmin = createUser(['role' => Role::SUPER_ADMIN]);

            expect($superAdmin->isSuperAdmin())->toBeTrue();
        });

        it('returns false for other roles', function () {
            $admin = createUser(['role' => Role::ADMIN]);

            expect($admin->isSuperAdmin())->toBeFalse();
        });
    });

    describe('permissions', function () {
        it('returns all permissions for the role', function () {
            $admin = createUser(['role' => Role::ADMIN]);
            $permissions = $admin->permissions();

            expect($permissions)->toBeArray();
            expect($permissions)->toContain(Permission::VIEW_BIKES);
            expect($permissions)->toContain(Permission::MANAGE_USERS);
        });
    });

    describe('updateFromKeycloak', function () {
        it('updates user information', function () {
            $user = createUser();
            $originalUpdatedAt = $user->updatedAt();

            // Wait a tiny bit to ensure different timestamp
            usleep(1000);

            $user->updateFromKeycloak(
                email: 'new.email@example.com',
                firstName: 'NewFirst',
                lastName: 'NewLast',
            );

            expect($user->email())->toBe('new.email@example.com');
            expect($user->firstName())->toBe('NewFirst');
            expect($user->lastName())->toBe('NewLast');
            expect($user->updatedAt())->not->toBe($originalUpdatedAt);
        });

        it('returns self for method chaining', function () {
            $user = createUser();
            $result = $user->updateFromKeycloak('new@example.com', 'New', 'Name');

            expect($result)->toBe($user);
        });
    });

    describe('changeRole', function () {
        it('changes the user role', function () {
            $user = createUser(['role' => Role::EMPLOYEE]);

            $user->changeRole(Role::MANAGER);

            expect($user->role())->toBe(Role::MANAGER);
        });

        it('updates the updatedAt timestamp', function () {
            $user = createUser();
            $originalUpdatedAt = $user->updatedAt();

            usleep(1000);
            $user->changeRole(Role::ADMIN);

            expect($user->updatedAt())->not->toBe($originalUpdatedAt);
        });

        it('returns self for method chaining', function () {
            $user = createUser();
            $result = $user->changeRole(Role::ADMIN);

            expect($result)->toBe($user);
        });
    });

    describe('assignToTenant', function () {
        it('assigns user to a tenant', function () {
            $user = createUser(['tenantId' => null]);

            $user->assignToTenant('new-tenant-id');

            expect($user->tenantId())->toBe('new-tenant-id');
        });

        it('can unassign user from tenant', function () {
            $user = createUser(['tenantId' => 'some-tenant']);

            $user->assignToTenant(null);

            expect($user->tenantId())->toBeNull();
        });

        it('returns self for method chaining', function () {
            $user = createUser();
            $result = $user->assignToTenant('tenant-id');

            expect($result)->toBe($user);
        });
    });

    describe('activate', function () {
        it('activates an inactive user', function () {
            $user = createUser(['isActive' => false]);

            $user->activate();

            expect($user->isActive())->toBeTrue();
        });

        it('keeps already active user active', function () {
            $user = createUser(['isActive' => true]);

            $user->activate();

            expect($user->isActive())->toBeTrue();
        });

        it('returns self for method chaining', function () {
            $user = createUser();
            $result = $user->activate();

            expect($result)->toBe($user);
        });
    });

    describe('deactivate', function () {
        it('deactivates an active user', function () {
            $user = createUser(['isActive' => true]);

            $user->deactivate();

            expect($user->isActive())->toBeFalse();
        });

        it('keeps already inactive user inactive', function () {
            $user = createUser(['isActive' => false]);

            $user->deactivate();

            expect($user->isActive())->toBeFalse();
        });

        it('returns self for method chaining', function () {
            $user = createUser();
            $result = $user->deactivate();

            expect($result)->toBe($user);
        });
    });

    describe('recordLogin', function () {
        it('records the login timestamp', function () {
            $user = createUser(['lastLoginAt' => null]);

            $user->recordLogin();

            expect($user->lastLoginAt())->toBeInstanceOf(\DateTimeImmutable::class);
        });

        it('updates the login timestamp', function () {
            $oldLogin = new \DateTimeImmutable('2024-01-01');
            $user = createUser(['lastLoginAt' => $oldLogin]);

            usleep(1000);
            $user->recordLogin();

            expect($user->lastLoginAt())->not->toBe($oldLogin);
        });

        it('returns self for method chaining', function () {
            $user = createUser();
            $result = $user->recordLogin();

            expect($result)->toBe($user);
        });
    });
});
