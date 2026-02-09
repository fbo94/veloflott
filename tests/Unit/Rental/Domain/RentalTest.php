<?php

declare(strict_types=1);

use Rental\Domain\RentalStatus;

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

test('active status blocks both calendar and bike', function () {
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
