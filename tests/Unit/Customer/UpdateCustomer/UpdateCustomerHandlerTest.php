<?php

declare(strict_types=1);

use Customer\Application\CreateCustomer\CustomerEmailAlreadyExistsException;
use Customer\Application\UpdateCustomer\UpdateCustomerCommand;
use Customer\Application\UpdateCustomer\UpdateCustomerHandler;
use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;

beforeEach(function () {
    $this->customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->handler = new UpdateCustomerHandler($this->customerRepository);
});

afterEach(function () {
    Mockery::close();
});

test('can update a customer with all fields', function () {
    // Arrange
    $existingCustomer = new Customer(
        id: 'customer-123',
        firstName: 'Old',
        lastName: 'Name',
        email: 'old@example.com',
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
        isRisky: false,
        createdAt: new DateTimeImmutable('2024-01-01'),
        updatedAt: new DateTimeImmutable('2024-01-01'),
    );

    $command = new UpdateCustomerCommand(
        customerId: 'customer-123',
        firstName: 'John',
        lastName: 'Doe',
        email: 'john.doe@example.com',
        phone: '+33612345678',
        identityDocumentType: 'passport',
        identityDocumentNumber: 'AB123456',
        height: 180,
        weight: 75,
        address: '123 Main St',
        notes: 'Updated notes',
        photos: ['https://example.com/photo1.jpg'],
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findById')
        ->once()
        ->with('customer-123')
        ->andReturn($existingCustomer);

    $this->customerRepository->shouldReceive('findByEmail')
        ->once()
        ->with('john.doe@example.com')
        ->andReturn(null);

    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) {
            return $customer->id() === 'customer-123'
                && $customer->firstName() === 'John'
                && $customer->lastName() === 'Doe'
                && $customer->email() === 'john.doe@example.com'
                && $customer->phone() === '+33612345678'
                && $customer->photos() === ['https://example.com/photo1.jpg'];
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
    expect($response->customerId)->toBe('customer-123');
    expect($response->message)->toBe('Customer updated successfully');
});

test('throws exception when customer does not exist', function () {
    // Arrange
    $command = new UpdateCustomerCommand(
        customerId: 'non-existent',
        firstName: 'John',
        lastName: 'Doe',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findById')
        ->once()
        ->with('non-existent')
        ->andReturn(null);

    // Act & Assert
    expect(fn () => $this->handler->handle($command))
        ->toThrow(DomainException::class, 'Customer with ID non-existent not found');
});

test('throws exception when email is already used by another customer', function () {
    // Arrange
    $existingCustomer = new Customer(
        id: 'customer-123',
        firstName: 'John',
        lastName: 'Doe',
        email: 'old@example.com',
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
        isRisky: false,
        createdAt: new DateTimeImmutable,
        updatedAt: new DateTimeImmutable,
    );

    $anotherCustomer = new Customer(
        id: 'customer-456',
        firstName: 'Jane',
        lastName: 'Smith',
        email: 'taken@example.com',
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
        isRisky: false,
        createdAt: new DateTimeImmutable,
        updatedAt: new DateTimeImmutable,
    );

    $command = new UpdateCustomerCommand(
        customerId: 'customer-123',
        firstName: 'John',
        lastName: 'Doe',
        email: 'taken@example.com', // Email already used by customer-456
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findById')
        ->once()
        ->with('customer-123')
        ->andReturn($existingCustomer);

    $this->customerRepository->shouldReceive('findByEmail')
        ->once()
        ->with('taken@example.com')
        ->andReturn($anotherCustomer);

    // Act & Assert
    expect(fn () => $this->handler->handle($command))
        ->toThrow(CustomerEmailAlreadyExistsException::class);
});

test('allows keeping the same email', function () {
    // Arrange
    $existingCustomer = new Customer(
        id: 'customer-789',
        firstName: 'John',
        lastName: 'Doe',
        email: 'same@example.com',
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
        isRisky: false,
        createdAt: new DateTimeImmutable,
        updatedAt: new DateTimeImmutable,
    );

    $command = new UpdateCustomerCommand(
        customerId: 'customer-789',
        firstName: 'John',
        lastName: 'Updated',
        email: 'same@example.com', // Same email as before
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findById')
        ->once()
        ->andReturn($existingCustomer);

    // findByEmail should NOT be called when email hasn't changed
    $this->customerRepository->shouldNotReceive('findByEmail');

    $this->customerRepository->shouldReceive('save')
        ->once();

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
});

test('can update customer photos', function () {
    // Arrange
    $existingCustomer = new Customer(
        id: 'customer-abc',
        firstName: 'Photo',
        lastName: 'Customer',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: ['https://example.com/old1.jpg', 'https://example.com/old2.jpg'],
        isRisky: false,
        createdAt: new DateTimeImmutable,
        updatedAt: new DateTimeImmutable,
    );

    $newPhotos = [
        'https://example.com/new1.jpg',
        'https://example.com/new2.jpg',
        'https://example.com/new3.jpg',
    ];

    $command = new UpdateCustomerCommand(
        customerId: 'customer-abc',
        firstName: 'Photo',
        lastName: 'Customer',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: $newPhotos,
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findById')
        ->once()
        ->andReturn($existingCustomer);

    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) use ($newPhotos) {
            return $customer->photos() === $newPhotos;
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
});

test('can clear customer photos', function () {
    // Arrange
    $existingCustomer = new Customer(
        id: 'customer-def',
        firstName: 'Clear',
        lastName: 'Photos',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: ['https://example.com/photo1.jpg'],
        isRisky: false,
        createdAt: new DateTimeImmutable,
        updatedAt: new DateTimeImmutable,
    );

    $command = new UpdateCustomerCommand(
        customerId: 'customer-def',
        firstName: 'Clear',
        lastName: 'Photos',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [], // Clear photos
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findById')
        ->once()
        ->andReturn($existingCustomer);

    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) {
            return $customer->photos() === [];
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
});
