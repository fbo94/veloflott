<?php

declare(strict_types=1);

use Customer\Domain\Customer;

test('can create a customer with all fields', function () {
    // Arrange & Act
    $customer = new Customer(
        id: 'customer-123',
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
        isRisky: false,
        createdAt: new DateTimeImmutable('2024-01-15 10:00:00'),
        updatedAt: new DateTimeImmutable('2024-01-15 10:00:00'),
    );

    // Assert
    expect($customer->id())->toBe('customer-123');
    expect($customer->firstName())->toBe('John');
    expect($customer->lastName())->toBe('Doe');
    expect($customer->fullName())->toBe('John Doe');
    expect($customer->email())->toBe('john.doe@example.com');
    expect($customer->phone())->toBe('+33612345678');
    expect($customer->identityDocumentType())->toBe('passport');
    expect($customer->identityDocumentNumber())->toBe('AB123456');
    expect($customer->height())->toBe(180);
    expect($customer->weight())->toBe(75);
    expect($customer->address())->toBe('123 Main St, Paris');
    expect($customer->notes())->toBe('VIP customer');
    expect($customer->photos())->toBe(['https://example.com/id-front.jpg', 'https://example.com/id-back.jpg']);
    expect($customer->isRisky())->toBeFalse();
});

test('can create a customer with minimal fields', function () {
    // Arrange & Act
    $customer = new Customer(
        id: 'customer-456',
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
        isRisky: false,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );

    // Assert
    expect($customer->id())->toBe('customer-456');
    expect($customer->firstName())->toBe('Jane');
    expect($customer->lastName())->toBe('Smith');
    expect($customer->fullName())->toBe('Jane Smith');
    expect($customer->email())->toBeNull();
    expect($customer->phone())->toBeNull();
    expect($customer->photos())->toBe([]);
    expect($customer->isRisky())->toBeFalse();
});

test('can update customer information', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-789',
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
        createdAt: new DateTimeImmutable('2024-01-01'),
        updatedAt: new DateTimeImmutable('2024-01-01'),
    );

    // Act
    $customer->update(
        firstName: 'Jonathan',
        lastName: 'Doe',
        email: 'new@example.com',
        phone: '+33612345678',
        identityDocumentType: 'passport',
        identityDocumentNumber: 'XY789',
        height: 185,
        weight: 80,
        address: '456 New Street',
        notes: 'Updated notes',
        photos: ['https://example.com/new-photo.jpg'],
    );

    // Assert
    expect($customer->firstName())->toBe('Jonathan');
    expect($customer->lastName())->toBe('Doe');
    expect($customer->email())->toBe('new@example.com');
    expect($customer->phone())->toBe('+33612345678');
    expect($customer->identityDocumentType())->toBe('passport');
    expect($customer->identityDocumentNumber())->toBe('XY789');
    expect($customer->height())->toBe(185);
    expect($customer->weight())->toBe(80);
    expect($customer->address())->toBe('456 New Street');
    expect($customer->notes())->toBe('Updated notes');
    expect($customer->photos())->toBe(['https://example.com/new-photo.jpg']);
});

test('can add a photo to customer', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-abc',
        firstName: 'Alice',
        lastName: 'Wonder',
        email: null,
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

    // Act
    $customer->addPhoto('https://example.com/photo1.jpg');
    $customer->addPhoto('https://example.com/photo2.jpg');

    // Assert
    expect($customer->photos())->toHaveCount(2);
    expect($customer->photos())->toContain('https://example.com/photo1.jpg');
    expect($customer->photos())->toContain('https://example.com/photo2.jpg');
});

test('does not add duplicate photo', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-def',
        firstName: 'Bob',
        lastName: 'Builder',
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
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );

    // Act
    $customer->addPhoto('https://example.com/photo1.jpg'); // Duplicate

    // Assert
    expect($customer->photos())->toHaveCount(1);
    expect($customer->photos())->toBe(['https://example.com/photo1.jpg']);
});

test('can remove a photo from customer', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-ghi',
        firstName: 'Charlie',
        lastName: 'Brown',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [
            'https://example.com/photo1.jpg',
            'https://example.com/photo2.jpg',
            'https://example.com/photo3.jpg',
        ],
        isRisky: false,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );

    // Act
    $customer->removePhoto('https://example.com/photo2.jpg');

    // Assert
    expect($customer->photos())->toHaveCount(2);
    expect($customer->photos())->not->toContain('https://example.com/photo2.jpg');
    expect($customer->photos())->toContain('https://example.com/photo1.jpg');
    expect($customer->photos())->toContain('https://example.com/photo3.jpg');
});

test('can mark customer as risky', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-jkl',
        firstName: 'David',
        lastName: 'Risk',
        email: null,
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

    // Act
    $customer->markAsRisky();

    // Assert
    expect($customer->isRisky())->toBeTrue();
});

test('can unmark customer as risky', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-mno',
        firstName: 'Eve',
        lastName: 'Safe',
        email: null,
        phone: null,
        identityDocumentType: null,
        identityDocumentNumber: null,
        height: null,
        weight: null,
        address: null,
        notes: null,
        photos: [],
        isRisky: true,
        createdAt: new DateTimeImmutable(),
        updatedAt: new DateTimeImmutable(),
    );

    // Act
    $customer->unmarkAsRisky();

    // Assert
    expect($customer->isRisky())->toBeFalse();
});

test('fullName returns correct format', function () {
    // Arrange
    $customer = new Customer(
        id: 'customer-pqr',
        firstName: 'Mary',
        lastName: 'Johnson',
        email: null,
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

    // Assert
    expect($customer->fullName())->toBe('Mary Johnson');
});
