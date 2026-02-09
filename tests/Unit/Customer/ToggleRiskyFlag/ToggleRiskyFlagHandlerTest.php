<?php

declare(strict_types=1);

use Customer\Application\ToggleRiskyFlag\ToggleRiskyFlagCommand;
use Customer\Application\ToggleRiskyFlag\ToggleRiskyFlagHandler;
use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->handler = new ToggleRiskyFlagHandler($this->repository);
});

afterEach(function () {
    Mockery::close();
});

test('marks customer as risky when not already risky', function () {
    // Arrange
    $customerId = 'customer-123';
    $command = new ToggleRiskyFlagCommand(customerId: $customerId);

    $customer = new Customer(
        id: $customerId,
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
    );

    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn($customer);

    $this->repository->shouldReceive('save')
        ->once()
        ->withArgs(function ($savedCustomer) {
            return $savedCustomer->isRisky() === true;
        })
        ->andReturn($customer);

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response->customerId)->toBe($customerId);
    expect($response->isRisky)->toBe(true);
    expect($response->message)->toBe('Customer marked as risky');
});

test('unmarks customer as risky when already risky', function () {
    // Arrange
    $customerId = 'customer-456';
    $command = new ToggleRiskyFlagCommand(customerId: $customerId);

    $customer = new Customer(
        id: $customerId,
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
        isRisky: true,
        createdAt: new \DateTimeImmutable(),
        updatedAt: new \DateTimeImmutable(),
    );

    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn($customer);

    $this->repository->shouldReceive('save')
        ->once()
        ->withArgs(function ($savedCustomer) {
            return $savedCustomer->isRisky() === false;
        })
        ->andReturn($customer);

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response->customerId)->toBe($customerId);
    expect($response->isRisky)->toBe(false);
    expect($response->message)->toBe('Customer unmarked as risky');
});

test('throws exception when customer not found', function () {
    // Arrange
    $customerId = 'non-existent';
    $command = new ToggleRiskyFlagCommand(customerId: $customerId);

    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn(null);

    $this->repository->shouldReceive('save')
        ->never();

    // Act & Assert
    expect(fn () => $this->handler->handle($command))
        ->toThrow(\DomainException::class, "Customer with ID {$customerId} not found");
});

test('saves customer after toggling flag', function () {
    // Arrange
    $customerId = 'customer-789';
    $command = new ToggleRiskyFlagCommand(customerId: $customerId);

    $customer = new Customer(
        id: $customerId,
        firstName: 'Test',
        lastName: 'Customer',
        email: 'test@example.com',
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

    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn($customer);

    $this->repository->shouldReceive('save')
        ->once()
        ->with(Mockery::type(Customer::class))
        ->andReturn($customer);

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response->customerId)->toBe($customerId);
});

test('toggles flag multiple times correctly', function () {
    // Arrange
    $customerId = 'customer-toggle';
    $command = new ToggleRiskyFlagCommand(customerId: $customerId);

    $customer = new Customer(
        id: $customerId,
        firstName: 'Toggle',
        lastName: 'Test',
        email: 'toggle@example.com',
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

    // First toggle: false -> true
    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn($customer);

    $this->repository->shouldReceive('save')
        ->once()
        ->andReturn($customer);

    $response1 = $this->handler->handle($command);
    expect($response1->isRisky)->toBe(true);
    expect($response1->message)->toBe('Customer marked as risky');

    // Second toggle: true -> false
    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn($customer);

    $this->repository->shouldReceive('save')
        ->once()
        ->andReturn($customer);

    $response2 = $this->handler->handle($command);
    expect($response2->isRisky)->toBe(false);
    expect($response2->message)->toBe('Customer unmarked as risky');
});
