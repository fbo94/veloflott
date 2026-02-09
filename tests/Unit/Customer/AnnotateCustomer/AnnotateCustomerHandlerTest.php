<?php

declare(strict_types=1);

use Customer\Application\AnnotateCustomer\AnnotateCustomerCommand;
use Customer\Application\AnnotateCustomer\AnnotateCustomerHandler;
use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;

beforeEach(function () {
    $this->repository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->handler = new AnnotateCustomerHandler($this->repository);
});

afterEach(function () {
    Mockery::close();
});

test('can annotate customer with annotation and mark as risky', function () {
    // Arrange
    $customerId = 'customer-123';
    $command = new AnnotateCustomerCommand(
        customerId: $customerId,
        annotation: 'Président !',
        isRiskyCustomer: true
    );

    $customer = new Customer(
        id: $customerId,
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
    );

    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn($customer);

    $this->repository->shouldReceive('save')
        ->once()
        ->withArgs(function ($savedCustomer) {
            return $savedCustomer->notes() === 'Président !'
                && $savedCustomer->isRisky() === true;
        })
        ->andReturn($customer);

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response->customerId)->toBe($customerId);
    expect($response->annotation)->toBe('Président !');
    expect($response->isRiskyCustomer)->toBe(true);
    expect($response->message)->toBe('Customer annotated successfully');
});

test('can annotate customer without marking as risky', function () {
    // Arrange
    $customerId = 'customer-456';
    $command = new AnnotateCustomerCommand(
        customerId: $customerId,
        annotation: 'Good customer',
        isRiskyCustomer: false
    );

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
            return $savedCustomer->notes() === 'Good customer'
                && $savedCustomer->isRisky() === false;
        })
        ->andReturn($customer);

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response->customerId)->toBe($customerId);
    expect($response->annotation)->toBe('Good customer');
    expect($response->isRiskyCustomer)->toBe(false);
});

test('can clear annotation', function () {
    // Arrange
    $customerId = 'customer-789';
    $command = new AnnotateCustomerCommand(
        customerId: $customerId,
        annotation: null,
        isRiskyCustomer: false
    );

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
        notes: 'Old annotation',
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
            return $savedCustomer->notes() === null
                && $savedCustomer->isRisky() === false;
        })
        ->andReturn($customer);

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response->customerId)->toBe($customerId);
    expect($response->annotation)->toBeNull();
    expect($response->isRiskyCustomer)->toBe(false);
});

test('throws exception when customer not found', function () {
    // Arrange
    $customerId = 'non-existent';
    $command = new AnnotateCustomerCommand(
        customerId: $customerId,
        annotation: 'Test',
        isRiskyCustomer: false
    );

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

test('saves customer after annotation', function () {
    // Arrange
    $customerId = 'customer-save';
    $command = new AnnotateCustomerCommand(
        customerId: $customerId,
        annotation: 'VIP',
        isRiskyCustomer: false
    );

    $customer = new Customer(
        id: $customerId,
        firstName: 'VIP',
        lastName: 'Customer',
        email: 'vip@example.com',
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
