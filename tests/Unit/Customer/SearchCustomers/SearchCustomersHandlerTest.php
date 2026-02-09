<?php

declare(strict_types=1);

use Customer\Application\SearchCustomers\SearchCustomersHandler;
use Customer\Application\SearchCustomers\SearchCustomersQuery;
use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->handler = new SearchCustomersHandler($this->repository);
});

afterEach(function () {
    Mockery::close();
});

test('returns all customers when no search term provided', function () {
    // Arrange
    $query = new SearchCustomersQuery(search: null);

    $customers = [
        new Customer(
            id: 'customer-1',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '+33612345678',
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
        ),
        new Customer(
            id: 'customer-2',
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'jane@example.com',
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
        ),
    ];

    $this->repository->shouldReceive('findAll')
        ->once()
        ->andReturn($customers);

    $this->repository->shouldReceive('search')
        ->never();

    // Act
    $response = $this->handler->handle($query);

    // Assert
    expect($response->customers)->toHaveCount(2);
    expect($response->customers[0]->id)->toBe('customer-1');
    expect($response->customers[0]->firstName)->toBe('John');
    expect($response->customers[1]->id)->toBe('customer-2');
    expect($response->customers[1]->firstName)->toBe('Jane');
});

test('returns all customers when empty search term provided', function () {
    // Arrange
    $query = new SearchCustomersQuery(search: '');

    $customers = [
        new Customer(
            id: 'customer-1',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
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
        ),
    ];

    $this->repository->shouldReceive('findAll')
        ->once()
        ->andReturn($customers);

    $this->repository->shouldReceive('search')
        ->never();

    // Act
    $response = $this->handler->handle($query);

    // Assert
    expect($response->customers)->toHaveCount(1);
});

test('searches customers when search term provided', function () {
    // Arrange
    $searchTerm = 'John';
    $query = new SearchCustomersQuery(search: $searchTerm);

    $customers = [
        new Customer(
            id: 'customer-1',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '+33612345678',
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
        ),
    ];

    $this->repository->shouldReceive('search')
        ->once()
        ->with($searchTerm)
        ->andReturn($customers);

    $this->repository->shouldReceive('findAll')
        ->never();

    // Act
    $response = $this->handler->handle($query);

    // Assert
    expect($response->customers)->toHaveCount(1);
    expect($response->customers[0]->firstName)->toBe('John');
});

test('returns empty array when no customers match search', function () {
    // Arrange
    $searchTerm = 'NonExistent';
    $query = new SearchCustomersQuery(search: $searchTerm);

    $this->repository->shouldReceive('search')
        ->once()
        ->with($searchTerm)
        ->andReturn([]);

    // Act
    $response = $this->handler->handle($query);

    // Assert
    expect($response->customers)->toBeEmpty();
});

test('converts customers to DTOs in response', function () {
    // Arrange
    $query = new SearchCustomersQuery(search: null);

    $customers = [
        new Customer(
            id: 'customer-1',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            phone: '+33612345678',
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
        ),
    ];

    $this->repository->shouldReceive('findAll')
        ->once()
        ->andReturn($customers);

    // Act
    $response = $this->handler->handle($query);

    // Assert
    expect($response->customers[0])->toBeInstanceOf(\Customer\Application\SearchCustomers\CustomerDto::class);
    expect($response->customers[0]->id)->toBe('customer-1');
    expect($response->customers[0]->fullName)->toBe('John Doe');
});
