<?php

declare(strict_types=1);

namespace Tests\Unit\Customer\Application;

use Customer\Application\CreateCustomer\CreateCustomerCommand;
use Customer\Application\CreateCustomer\CreateCustomerHandler;
use Customer\Application\CreateCustomer\CreateCustomerResponse;
use Customer\Application\CreateCustomer\CustomerEmailAlreadyExistsException;
use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;
use Mockery;

describe('CreateCustomerHandler', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('creates a customer successfully', function () {
        $customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
        $customerRepository->shouldReceive('findByEmail')
            ->with('john@example.com')
            ->once()
            ->andReturn(null);
        $customerRepository->shouldReceive('save')
            ->once();

        $handler = new CreateCustomerHandler($customerRepository);
        $command = new CreateCustomerCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '+33612345678',
            identityDocumentType: 'passport',
            identityDocumentNumber: '123456789',
            height: 180,
            weight: 75,
            address: '123 Main St',
            notes: 'Regular customer',
        );

        $response = $handler->handle($command);

        expect($response)->toBeInstanceOf(CreateCustomerResponse::class);
        expect($response->firstName)->toBe('John');
        expect($response->lastName)->toBe('Doe');
        expect($response->email)->toBe('john@example.com');
    });

    it('throws exception when email already exists', function () {
        $existingCustomer = new Customer(
            id: 'existing-123',
            firstName: 'Existing',
            lastName: 'Customer',
            email: 'existing@example.com',
            phone: null,
            identityDocumentType: null,
            identityDocumentNumber: null,
            height: null,
            weight: null,
            address: null,
            notes: null,
            photos: [],
            isRisky: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
        $customerRepository->shouldReceive('findByEmail')
            ->with('existing@example.com')
            ->once()
            ->andReturn($existingCustomer);

        $handler = new CreateCustomerHandler($customerRepository);
        $command = new CreateCustomerCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: 'existing@example.com',
            phone: null,
            identityDocumentType: null,
            identityDocumentNumber: null,
            height: null,
            weight: null,
            address: null,
            notes: null,
        );

        expect(fn () => $handler->handle($command))
            ->toThrow(CustomerEmailAlreadyExistsException::class);
    });

    it('creates customer without email', function () {
        $customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
        $customerRepository->shouldReceive('save')->once();

        $handler = new CreateCustomerHandler($customerRepository);
        $command = new CreateCustomerCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: null,
            phone: '+33612345678',
            identityDocumentType: null,
            identityDocumentNumber: null,
            height: null,
            weight: null,
            address: null,
            notes: null,
        );

        $response = $handler->handle($command);

        expect($response->email)->toBeNull();
        expect($response->phone)->toBe('+33612345678');
    });
});

describe('CreateCustomerCommand', function () {
    it('holds all customer data', function () {
        $command = new CreateCustomerCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '+33612345678',
            identityDocumentType: 'passport',
            identityDocumentNumber: '123456789',
            height: 180,
            weight: 75,
            address: '123 Main St',
            notes: 'Test notes',
            photos: ['photo1.jpg'],
        );

        expect($command->firstName)->toBe('John');
        expect($command->lastName)->toBe('Doe');
        expect($command->email)->toBe('john@example.com');
        expect($command->photos)->toBe(['photo1.jpg']);
    });
});

describe('CreateCustomerResponse', function () {
    it('converts to array', function () {
        $response = new CreateCustomerResponse(
            id: 'customer-123',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '+33612345678',
        );

        $array = $response->toArray();

        expect($array)->toHaveKeys(['id', 'first_name', 'last_name', 'full_name', 'email', 'phone']);
        expect($array['full_name'])->toBe('John Doe');
    });
});
