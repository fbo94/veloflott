<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application;

use Auth\Application\ListUsers\ListUsersHandler;
use Auth\Application\ListUsers\ListUsersQuery;
use Auth\Application\ListUsers\ListUsersResponse;
use Auth\Domain\Role;
use Auth\Domain\User;
use Auth\Domain\UserRepositoryInterface;
use Mockery;

describe('ListUsersHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('returns list of users', function () {
        $users = [
            new User(
                id: 'user-1',
                keycloakId: 'keycloak-1',
                tenantId: 'tenant-1',
                email: 'user1@example.com',
                firstName: 'John',
                lastName: 'Doe',
                role: Role::ADMIN,
                isActive: true,
            ),
            new User(
                id: 'user-2',
                keycloakId: 'keycloak-2',
                tenantId: 'tenant-1',
                email: 'user2@example.com',
                firstName: 'Jane',
                lastName: 'Smith',
                role: Role::MANAGER,
                isActive: true,
            ),
        ];

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findAll')
            ->with(null, null)
            ->once()
            ->andReturn($users);

        $handler = new ListUsersHandler($userRepository);
        $query = new ListUsersQuery();

        $response = $handler->handle($query);

        expect($response)->toBeInstanceOf(ListUsersResponse::class);
        expect($response->total)->toBe(2);
        expect($response->users)->toHaveCount(2);
    });

    it('filters users by role', function () {
        $users = [
            new User(
                id: 'user-1',
                keycloakId: 'keycloak-1',
                tenantId: 'tenant-1',
                email: 'admin@example.com',
                firstName: 'Admin',
                lastName: 'User',
                role: Role::ADMIN,
                isActive: true,
            ),
        ];

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findAll')
            ->with(Role::ADMIN, null)
            ->once()
            ->andReturn($users);

        $handler = new ListUsersHandler($userRepository);
        $query = new ListUsersQuery(role: Role::ADMIN);

        $response = $handler->handle($query);

        expect($response->total)->toBe(1);
        expect($response->users[0]['role'])->toBe('admin');
    });

    it('filters users by active status', function () {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findAll')
            ->with(null, true)
            ->once()
            ->andReturn([]);

        $handler = new ListUsersHandler($userRepository);
        $query = new ListUsersQuery(isActive: true);

        $response = $handler->handle($query);

        expect($response->total)->toBe(0);
    });
});

describe('ListUsersResponse', function () {
    it('creates response from users array', function () {
        $users = [
            new User(
                id: 'user-1',
                keycloakId: 'keycloak-1',
                tenantId: 'tenant-1',
                email: 'user1@example.com',
                firstName: 'John',
                lastName: 'Doe',
                role: Role::ADMIN,
                isActive: true,
            ),
        ];

        $response = ListUsersResponse::fromUsers($users);

        expect($response->total)->toBe(1);
        expect($response->users[0]['id'])->toBe('user-1');
        expect($response->users[0]['email'])->toBe('user1@example.com');
        expect($response->users[0]['full_name'])->toBe('John Doe');
    });

    it('converts to array', function () {
        $users = [
            new User(
                id: 'user-1',
                keycloakId: 'keycloak-1',
                tenantId: 'tenant-1',
                email: 'user1@example.com',
                firstName: 'John',
                lastName: 'Doe',
                role: Role::ADMIN,
                isActive: true,
            ),
        ];

        $response = ListUsersResponse::fromUsers($users);
        $array = $response->toArray();

        expect($array)->toHaveKeys(['users', 'total']);
        expect($array['total'])->toBe(1);
    });
});

describe('ListUsersQuery', function () {
    it('holds role and isActive filters', function () {
        $query = new ListUsersQuery(role: Role::MANAGER, isActive: true);

        expect($query->role)->toBe(Role::MANAGER);
        expect($query->isActive)->toBeTrue();
    });

    it('defaults to null filters', function () {
        $query = new ListUsersQuery();

        expect($query->role)->toBeNull();
        expect($query->isActive)->toBeNull();
    });
});
