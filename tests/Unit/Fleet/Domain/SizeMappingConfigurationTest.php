<?php

declare(strict_types=1);

use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\SizeMappingConfiguration;
use Fleet\Domain\SizeRange;

test('can create size mapping configuration with all sizes', function () {
    // Arrange & Act
    $config = new SizeMappingConfiguration(
        id: 'config-123',
        version: 1,
        isActive: true,
        xsCm: new SizeRange(48, 50),
        xsInch: new SizeRange(13, 14),
        sCm: new SizeRange(51, 53),
        sInch: new SizeRange(15, 16),
        mCm: new SizeRange(54, 56),
        mInch: new SizeRange(17, 18),
        lCm: new SizeRange(57, 59),
        lInch: new SizeRange(19, 20),
        xlCm: new SizeRange(60, 62),
        xlInch: new SizeRange(21, 22),
        xxlCm: new SizeRange(63, 999),
        xxlInch: new SizeRange(23, 999),
        createdAt: new DateTimeImmutable('2024-01-01'),
        updatedAt: new DateTimeImmutable('2024-01-01'),
    );

    // Assert
    expect($config->id())->toBe('config-123');
    expect($config->version())->toBe(1);
    expect($config->isActive())->toBeTrue();
    expect($config->xsCm()->min())->toBe(48);
    expect($config->xsInch()->max())->toBe(14);
});

test('can create default configuration', function () {
    // Arrange & Act
    $config = SizeMappingConfiguration::createDefault(id: 'default-config');

    // Assert
    expect($config->version())->toBe(1);
    expect($config->isActive())->toBeTrue();
    expect($config->xsCm())->toEqual(new SizeRange(48, 50));
    expect($config->sCm())->toEqual(new SizeRange(51, 53));
    expect($config->mCm())->toEqual(new SizeRange(54, 56));
    expect($config->lCm())->toEqual(new SizeRange(57, 59));
    expect($config->xlCm())->toEqual(new SizeRange(60, 62));
    expect($config->xxlCm())->toEqual(new SizeRange(63, 999));
    expect($config->xsInch())->toEqual(new SizeRange(13, 14));
    expect($config->sInch())->toEqual(new SizeRange(15, 16));
    expect($config->mInch())->toEqual(new SizeRange(17, 18));
    expect($config->lInch())->toEqual(new SizeRange(19, 20));
    expect($config->xlInch())->toEqual(new SizeRange(21, 22));
    expect($config->xxlInch())->toEqual(new SizeRange(23, 999));
});

test('can get range for specific size letter in cm', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test');

    // Act & Assert
    expect($config->getRangeForSize(FrameSizeLetter::XS, 'cm'))->toEqual(new SizeRange(48, 50));
    expect($config->getRangeForSize(FrameSizeLetter::S, 'cm'))->toEqual(new SizeRange(51, 53));
    expect($config->getRangeForSize(FrameSizeLetter::M, 'cm'))->toEqual(new SizeRange(54, 56));
    expect($config->getRangeForSize(FrameSizeLetter::L, 'cm'))->toEqual(new SizeRange(57, 59));
    expect($config->getRangeForSize(FrameSizeLetter::XL, 'cm'))->toEqual(new SizeRange(60, 62));
    expect($config->getRangeForSize(FrameSizeLetter::XXL, 'cm'))->toEqual(new SizeRange(63, 999));
});

test('can get range for specific size letter in inches', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test');

    // Act & Assert
    expect($config->getRangeForSize(FrameSizeLetter::XS, 'inch'))->toEqual(new SizeRange(13, 14));
    expect($config->getRangeForSize(FrameSizeLetter::S, 'inch'))->toEqual(new SizeRange(15, 16));
    expect($config->getRangeForSize(FrameSizeLetter::M, 'inch'))->toEqual(new SizeRange(17, 18));
    expect($config->getRangeForSize(FrameSizeLetter::L, 'inch'))->toEqual(new SizeRange(19, 20));
    expect($config->getRangeForSize(FrameSizeLetter::XL, 'inch'))->toEqual(new SizeRange(21, 22));
    expect($config->getRangeForSize(FrameSizeLetter::XXL, 'inch'))->toEqual(new SizeRange(23, 999));
});

test('can calculate letter size from numeric value in cm', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test');

    // Act & Assert
    expect($config->letterFromNumeric(48, 'cm'))->toBe(FrameSizeLetter::XS);
    expect($config->letterFromNumeric(50, 'cm'))->toBe(FrameSizeLetter::XS);
    expect($config->letterFromNumeric(51, 'cm'))->toBe(FrameSizeLetter::S);
    expect($config->letterFromNumeric(55, 'cm'))->toBe(FrameSizeLetter::M);
    expect($config->letterFromNumeric(58, 'cm'))->toBe(FrameSizeLetter::L);
    expect($config->letterFromNumeric(61, 'cm'))->toBe(FrameSizeLetter::XL);
    expect($config->letterFromNumeric(70, 'cm'))->toBe(FrameSizeLetter::XXL);
});

test('can calculate letter size from numeric value in inches', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test');

    // Act & Assert
    expect($config->letterFromNumeric(13, 'inch'))->toBe(FrameSizeLetter::XS);
    expect($config->letterFromNumeric(14, 'inch'))->toBe(FrameSizeLetter::XS);
    expect($config->letterFromNumeric(15, 'inch'))->toBe(FrameSizeLetter::S);
    expect($config->letterFromNumeric(17, 'inch'))->toBe(FrameSizeLetter::M);
    expect($config->letterFromNumeric(19, 'inch'))->toBe(FrameSizeLetter::L);
    expect($config->letterFromNumeric(21, 'inch'))->toBe(FrameSizeLetter::XL);
    expect($config->letterFromNumeric(25, 'inch'))->toBe(FrameSizeLetter::XXL);
});

test('returns xxl when no matching range found', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test');

    // Act & Assert
    expect($config->letterFromNumeric(1000, 'cm'))->toBe(FrameSizeLetter::XXL);
    expect($config->letterFromNumeric(1, 'cm'))->toBe(FrameSizeLetter::XXL);
});

test('can update configuration to create new version', function () {
    // Arrange
    $original = SizeMappingConfiguration::createDefault(id: 'original');

    // Act
    $updated = $original->update(
        xsCm: new SizeRange(45, 48),
        xsInch: new SizeRange(12, 13),
        sCm: new SizeRange(49, 52),
        sInch: new SizeRange(14, 15),
        mCm: new SizeRange(53, 56),
        mInch: new SizeRange(16, 18),
        lCm: new SizeRange(57, 60),
        lInch: new SizeRange(19, 21),
        xlCm: new SizeRange(61, 64),
        xlInch: new SizeRange(22, 24),
        xxlCm: new SizeRange(65, 999),
        xxlInch: new SizeRange(25, 999),
    );

    // Assert
    expect($updated->version())->toBe(2);
    expect($updated->isActive())->toBeTrue();
    expect($updated->xsCm())->toEqual(new SizeRange(45, 48));
    expect($original->isActive())->toBeFalse(); // Original should be deactivated
});

test('can deactivate configuration', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test');
    expect($config->isActive())->toBeTrue();

    // Act
    $config->deactivate();

    // Assert
    expect($config->isActive())->toBeFalse();
});

test('converts to array correctly', function () {
    // Arrange
    $config = SizeMappingConfiguration::createDefault(id: 'test-123');

    // Act
    $array = $config->toArray();

    // Assert
    expect($array)->toHaveKeys(['id', 'version', 'is_active', 'sizes', 'created_at', 'updated_at']);
    expect($array['version'])->toBe(1);
    expect($array['sizes'])->toHaveCount(6);

    // Verify first size (XS)
    expect($array['sizes'][0])->toBe([
        'letter' => 'xs',
        'label' => 'XS',
        'cm' => ['min' => 48, 'max' => 50],
        'inch' => ['min' => 13, 'max' => 14],
    ]);

    // Verify last size (XXL)
    expect($array['sizes'][5])->toBe([
        'letter' => 'xxl',
        'label' => 'XXL',
        'cm' => ['min' => 63, 'max' => 999],
        'inch' => ['min' => 23, 'max' => 999],
    ]);
});
