<?php

declare(strict_types=1);

use Rental\Domain\RentalStatus;

test('rental status has correct values', function () {
    expect(RentalStatus::PENDING->value)->toBe('pending');
    expect(RentalStatus::ACTIVE->value)->toBe('active');
    expect(RentalStatus::COMPLETED->value)->toBe('completed');
    expect(RentalStatus::CANCELLED->value)->toBe('cancelled');
});

test('pending rental can start', function () {
    $status = RentalStatus::PENDING;

    expect($status->canStart())->toBeTrue();
    expect($status->canCheckOut())->toBeFalse();
    expect($status->canCancel())->toBeTrue();
});

test('active rental can checkout', function () {
    $status = RentalStatus::ACTIVE;

    expect($status->canStart())->toBeFalse();
    expect($status->canCheckOut())->toBeTrue();
    expect($status->canCancel())->toBeFalse();
});

test('completed rental cannot perform actions', function () {
    $status = RentalStatus::COMPLETED;

    expect($status->canStart())->toBeFalse();
    expect($status->canCheckOut())->toBeFalse();
    expect($status->canCancel())->toBeFalse();
});
