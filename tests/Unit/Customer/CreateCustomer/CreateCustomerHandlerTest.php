<?php

declare(strict_types=1);

use Customer\Application\CreateCustomer\CreateCustomerCommand;
use Customer\Application\CreateCustomer\CreateCustomerHandler;
use Customer\Application\CreateCustomer\CustomerEmailAlreadyExistsException;
use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;

beforeEach(function () {
    $this->customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->handler = new CreateCustomerHandler($this->customerRepository);
});

afterEach(function () {
    Mockery::close();
});

test('can create a customer with all fields', function () {
    // Arrange
    $command = new CreateCustomerCommand(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john.doe@example.com',
        phone: '+33612345678',
        identityDocumentType: 'passport',
        identityDocumentNumber: 'AB123456',
        height: 180,
        weight: 75,
        address: '123 Main St, Paris',
        notes: 'VIP customer',
        photos: ['https://example.com/id-front.jpg', 'https://example.com/id-back.jpg'],
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findByEmail')
        ->once()
        ->with('john.doe@example.com')
        ->andReturn(null);

    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) {
            return $customer->firstName() === 'John'
                && $customer->lastName() === 'Doe'
                && $customer->email() === 'john.doe@example.com'
                && $customer->phone() === '+33612345678'
                && $customer->photos() === ['https://example.com/id-front.jpg', 'https://example.com/id-back.jpg']
                && $customer->isRisky() === false;
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
    expect($response->id)->not->toBeEmpty();
    expect($response->firstName)->toBe('John');
    expect($response->lastName)->toBe('Doe');
});

test('can create a customer with minimal fields', function () {
    // Arrange
    $command = new CreateCustomerCommand(
        firstName: 'Jane',
        lastName: 'Smith',
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
    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) {
            return $customer->firstName() === 'Jane'
                && $customer->lastName() === 'Smith'
                && $customer->email() === null
                && $customer->photos() === []
                && $customer->isRisky() === false;
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
    expect($response->firstName)->toBe('Jane');
    expect($response->lastName)->toBe('Smith');
});

test('throws exception when email already exists', function () {
    // Arrange
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
        photos: [],
    );

    $existingCustomer = new Customer(
        id: 'existing-customer',
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
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findByEmail')
        ->once()
        ->with('existing@example.com')
        ->andReturn($existingCustomer);

    // Act & Assert
    expect(fn () => $this->handler->handle($command))
        ->toThrow(CustomerEmailAlreadyExistsException::class);
});

test('allows null email without checking uniqueness', function () {
    // Arrange
    $command = new CreateCustomerCommand(
        firstName: 'Anonymous',
        lastName: 'User',
        email: null,
        phone: '+33612345678',
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
    );

    // Mock expectations - findByEmail should NOT be called when email is null
    $this->customerRepository->shouldNotReceive('findByEmail');

    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) {
            return $customer->email() === null;
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
    expect($response->firstName)->toBe('Anonymous');
});

test('creates customer with photos', function () {
    // Arrange
    $photos = [
        'https://storage.googleapis.com/bucket/photo1.jpg',
        'https://storage.googleapis.com/bucket/photo2.jpg',
    ];

    $command = new CreateCustomerCommand(
        firstName: 'Photo',
        lastName: 'Customer',
        email: 'photo@example.com',
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: $photos,
    );

    // Mock expectations
    $this->customerRepository->shouldReceive('findByEmail')
        ->once()
        ->andReturn(null);

    $this->customerRepository->shouldReceive('save')
        ->once()
        ->withArgs(function (Customer $customer) use ($photos) {
            return $customer->photos() === $photos;
        });

    // Act
    $response = $this->handler->handle($command);

    // Assert
    expect($response)->not->toBeNull();
});
