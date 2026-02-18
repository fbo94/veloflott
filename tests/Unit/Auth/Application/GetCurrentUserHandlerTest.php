<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application;

use Auth\Application\GetCurrentUser\GetCurrentUserHandler;
use Auth\Application\GetCurrentUser\GetCurrentUserQuery;
use Auth\Application\GetCurrentUser\GetCurrentUserResponse;
use Auth\Application\GetCurrentUser\UserNotFoundException;
use Auth\Domain\Role;
use Auth\Domain\User;
use Auth\Domain\UserRepositoryInterface;
use Mockery;
use Tenant\Application\TenantContext;

describe('GetCurrentUserHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('returns user response when user exists', function () {
        $user = new User(
            id: 'user-123',
            keycloakId: 'keycloak-456',
            tenantId: 'tenant-789',
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe',
            role: Role::ADMIN,
            isActive: true,
        );

        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findById')
            ->with('user-123')
            ->once()
            ->andReturn($user);

        $tenantContext = new TenantContext();

        $handler = new GetCurrentUserHandler($userRepository, $tenantContext);
        $query = new GetCurrentUserQuery('user-123');

        $response = $handler->handle($query);

        expect($response)->toBeInstanceOf(GetCurrentUserResponse::class);
        expect($response->id)->toBe('user-123');
        expect($response->email)->toBe('test@example.com');
        expect($response->firstName)->toBe('John');
        expect($response->lastName)->toBe('Doe');
        expect($response->role)->toBe('admin');
    });

    it('throws exception when user not found', function () {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userRepository->shouldReceive('findById')
            ->with('nonexistent')
            ->once()
            ->andReturn(null);

        $tenantContext = new TenantContext();

        $handler = new GetCurrentUserHandler($userRepository, $tenantContext);
        $query = new GetCurrentUserQuery('nonexistent');

        expect(fn () => $handler->handle($query))
            ->toThrow(UserNotFoundException::class);
    });
});

describe('GetCurrentUserResponse', function () {
    it('creates response from user', function () {
        $user = new User(
            id: 'user-123',
            keycloakId: 'keycloak-456',
            tenantId: 'tenant-789',
            email: 'test@example.com',
            firstName: 'Jane',
            lastName: 'Smith',
            role: Role::MANAGER,
            isActive: true,
        );

        $response = GetCurrentUserResponse::fromUser($user);

        expect($response->id)->toBe('user-123');
        expect($response->email)->toBe('test@example.com');
        expect($response->fullName)->toBe('Jane Smith');
        expect($response->role)->toBe('manager');
        expect($response->roleLabel)->toBe('Manager');
        expect($response->isActive)->toBeTrue();
        expect($response->tenant)->toBeNull();
    });

    it('converts to array', function () {
        $user = new User(
            id: 'user-123',
            keycloakId: 'keycloak-456',
            tenantId: 'tenant-789',
            email: 'test@example.com',
            firstName: 'Jane',
            lastName: 'Smith',
            role: Role::MANAGER,
            isActive: true,
        );

        $response = GetCurrentUserResponse::fromUser($user);
        $array = $response->toArray();

        expect($array)->toHaveKeys(['id', 'email', 'first_name', 'last_name', 'full_name', 'role', 'role_label', 'is_active', 'permissions']);
        expect($array['id'])->toBe('user-123');
        expect($array['email'])->toBe('test@example.com');
    });
});

describe('GetCurrentUserQuery', function () {
    it('holds user id', function () {
        $query = new GetCurrentUserQuery('user-123');

        expect($query->userId)->toBe('user-123');
    });
});
