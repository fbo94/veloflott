<?php

declare(strict_types=1);

use Rental\Domain\RentalStatus;

test('rental status has correct values', function () {
    expect(RentalStatus::RESERVED->value)->toBe('reserved');
    expect(RentalStatus::PENDING->value)->toBe('pending');
    expect(RentalStatus::ACTIVE->value)->toBe('active');
    expect(RentalStatus::COMPLETED->value)->toBe('completed');
    expect(RentalStatus::CANCELLED->value)->toBe('cancelled');
});

test('reserved rental can be confirmed', function () {
    $status = RentalStatus::RESERVED;

    expect($status->canConfirm())->toBeTrue();
    expect($status->canStart())->toBeFalse();
    expect($status->canCheckOut())->toBeFalse();
    expect($status->canCancel())->toBeTrue();
});

test('pending rental can start', function () {
    $status = RentalStatus::PENDING;

    expect($status->canConfirm())->toBeFalse();
    expect($status->canStart())->toBeTrue();
    expect($status->canCheckOut())->toBeFalse();
    expect($status->canCancel())->toBeTrue();
});

test('active rental can checkout and early return', function () {
    $status = RentalStatus::ACTIVE;

    expect($status->canStart())->toBeFalse();
    expect($status->canCheckOut())->toBeTrue();
    expect($status->canEarlyReturn())->toBeTrue();
    expect($status->canCancel())->toBeFalse();
});

test('completed rental cannot perform actions', function () {
    $status = RentalStatus::COMPLETED;

    expect($status->canConfirm())->toBeFalse();
    expect($status->canStart())->toBeFalse();
    expect($status->canCheckOut())->toBeFalse();
    expect($status->canCancel())->toBeFalse();
    expect($status->canEarlyReturn())->toBeFalse();
});

test('reserved status blocks calendar dates but not bike physically', function () {
    $status = RentalStatus::RESERVED;

    expect($status->blocksCalendarDates())->toBeTrue();
    expect($status->blocksBikePhysically())->toBeFalse();
});

test('pending status blocks calendar dates but not bike physically', function () {
    $status = RentalStatus::PENDING;

    expect($status->blocksCalendarDates())->toBeTrue();
    expect($status->blocksBikePhysically())->toBeFalse();
});

test('active status blocks both calendar and bike physically', function () {
    $status = RentalStatus::ACTIVE;

    expect($status->blocksCalendarDates())->toBeTrue();
    expect($status->blocksBikePhysically())->toBeTrue();
});

test('completed status does not block calendar or bike', function () {
    $status = RentalStatus::COMPLETED;

    expect($status->blocksCalendarDates())->toBeFalse();
    expect($status->blocksBikePhysically())->toBeFalse();
});

test('cancelled status does not block calendar or bike', function () {
    $status = RentalStatus::CANCELLED;

    expect($status->blocksCalendarDates())->toBeFalse();
    expect($status->blocksBikePhysically())->toBeFalse();
});
