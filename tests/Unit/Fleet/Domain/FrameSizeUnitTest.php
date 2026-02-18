<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\FrameSizeUnit;

describe('FrameSizeUnit', function () {
    describe('label', function () {
        it('returns correct label for letter', function () {
            expect(FrameSizeUnit::LETTER->label())->toBe('Lettres (XXS, XS, S, M, L, XL, XXL)');
        });

        it('returns correct label for cm', function () {
            expect(FrameSizeUnit::CM->label())->toBe('Centimètres');
        });

        it('returns correct label for inch', function () {
            expect(FrameSizeUnit::INCH->label())->toBe('Pouces');
        });
    });

    describe('cases', function () {
        it('returns all frame size units', function () {
            $cases = FrameSizeUnit::cases();

            expect($cases)->toHaveCount(3);
            expect($cases)->toContain(FrameSizeUnit::LETTER);
            expect($cases)->toContain(FrameSizeUnit::CM);
            expect($cases)->toContain(FrameSizeUnit::INCH);
        });
    });

    describe('from', function () {
        it('creates unit from valid string', function () {
            expect(FrameSizeUnit::from('letter'))->toBe(FrameSizeUnit::LETTER);
            expect(FrameSizeUnit::from('cm'))->toBe(FrameSizeUnit::CM);
            expect(FrameSizeUnit::from('inch'))->toBe(FrameSizeUnit::INCH);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => FrameSizeUnit::from('invalid'))->toThrow(\ValueError::class);
        });
    });
});
