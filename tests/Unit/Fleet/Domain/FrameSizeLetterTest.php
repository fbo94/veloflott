<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;

describe('FrameSizeLetter', function () {
    describe('label', function () {
        it('returns uppercase label', function () {
            expect(FrameSizeLetter::XS->label())->toBe('XS');
            expect(FrameSizeLetter::S->label())->toBe('S');
            expect(FrameSizeLetter::M->label())->toBe('M');
            expect(FrameSizeLetter::L->label())->toBe('L');
            expect(FrameSizeLetter::XL->label())->toBe('XL');
            expect(FrameSizeLetter::XXL->label())->toBe('XXL');
        });
    });

    describe('defaultCmRange', function () {
        it('returns correct range for XS', function () {
            $range = FrameSizeLetter::XS->defaultCmRange();
            expect($range)->toBe(['min' => 48, 'max' => 50]);
        });

        it('returns correct range for M', function () {
            $range = FrameSizeLetter::M->defaultCmRange();
            expect($range)->toBe(['min' => 54, 'max' => 56]);
        });

        it('returns correct range for XXL', function () {
            $range = FrameSizeLetter::XXL->defaultCmRange();
            expect($range)->toBe(['min' => 63, 'max' => 999]);
        });
    });

    describe('defaultInchRange', function () {
        it('returns correct range for XS', function () {
            $range = FrameSizeLetter::XS->defaultInchRange();
            expect($range)->toBe(['min' => 13, 'max' => 14]);
        });

        it('returns correct range for M', function () {
            $range = FrameSizeLetter::M->defaultInchRange();
            expect($range)->toBe(['min' => 17, 'max' => 18]);
        });

        it('returns correct range for XXL', function () {
            $range = FrameSizeLetter::XXL->defaultInchRange();
            expect($range)->toBe(['min' => 23, 'max' => 999]);
        });
    });

    describe('fromNumeric', function () {
        it('calculates letter size from cm', function () {
            expect(FrameSizeLetter::fromNumeric(49.0, FrameSizeUnit::CM))->toBe(FrameSizeLetter::XS);
            expect(FrameSizeLetter::fromNumeric(55.0, FrameSizeUnit::CM))->toBe(FrameSizeLetter::M);
            expect(FrameSizeLetter::fromNumeric(60.0, FrameSizeUnit::CM))->toBe(FrameSizeLetter::XL);
        });

        it('calculates letter size from inches', function () {
            expect(FrameSizeLetter::fromNumeric(14.0, FrameSizeUnit::INCH))->toBe(FrameSizeLetter::XS);
            expect(FrameSizeLetter::fromNumeric(17.0, FrameSizeUnit::INCH))->toBe(FrameSizeLetter::M);
            expect(FrameSizeLetter::fromNumeric(21.0, FrameSizeUnit::INCH))->toBe(FrameSizeLetter::XL);
        });

        it('returns XXL for very large sizes', function () {
            expect(FrameSizeLetter::fromNumeric(70.0, FrameSizeUnit::CM))->toBe(FrameSizeLetter::XXL);
            expect(FrameSizeLetter::fromNumeric(25.0, FrameSizeUnit::INCH))->toBe(FrameSizeLetter::XXL);
        });

        it('throws exception when using letter unit', function () {
            expect(fn () => FrameSizeLetter::fromNumeric(50.0, FrameSizeUnit::LETTER))
                ->toThrow(\InvalidArgumentException::class, 'Cannot calculate letter size from letter unit');
        });
    });

    describe('cases', function () {
        it('returns all letter sizes', function () {
            $cases = FrameSizeLetter::cases();

            expect($cases)->toHaveCount(6);
            expect($cases)->toContain(FrameSizeLetter::XS);
            expect($cases)->toContain(FrameSizeLetter::M);
            expect($cases)->toContain(FrameSizeLetter::XXL);
        });
    });

    describe('from', function () {
        it('creates letter from valid string', function () {
            expect(FrameSizeLetter::from('xs'))->toBe(FrameSizeLetter::XS);
            expect(FrameSizeLetter::from('m'))->toBe(FrameSizeLetter::M);
            expect(FrameSizeLetter::from('xxl'))->toBe(FrameSizeLetter::XXL);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => FrameSizeLetter::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
