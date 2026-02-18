<?php

declare(strict_types=1);

namespace Tests\Unit\Fleet\Domain;

use Fleet\Domain\FrameSize;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;

describe('FrameSize', function () {
    describe('fromLetter', function () {
        it('creates frame size from letter', function () {
            $frameSize = FrameSize::fromLetter(FrameSizeLetter::M);

            expect($frameSize->unit)->toBe(FrameSizeUnit::LETTER);
            expect($frameSize->letterValue)->toBe(FrameSizeLetter::M);
            expect($frameSize->letterEquivalent)->toBe(FrameSizeLetter::M);
            expect($frameSize->numericValue)->toBeNull();
        });
    });

    describe('fromCentimeters', function () {
        it('creates frame size from cm', function () {
            $frameSize = FrameSize::fromCentimeters(55.0);

            expect($frameSize->unit)->toBe(FrameSizeUnit::CM);
            expect($frameSize->numericValue)->toBe(55.0);
            expect($frameSize->letterValue)->toBeNull();
            expect($frameSize->letterEquivalent)->toBe(FrameSizeLetter::M);
        });
    });

    describe('fromInches', function () {
        it('creates frame size from inches', function () {
            $frameSize = FrameSize::fromInches(17.0);

            expect($frameSize->unit)->toBe(FrameSizeUnit::INCH);
            expect($frameSize->numericValue)->toBe(17.0);
            expect($frameSize->letterValue)->toBeNull();
            expect($frameSize->letterEquivalent)->toBe(FrameSizeLetter::M);
        });
    });

    describe('fromRequest', function () {
        it('creates frame size from letter request', function () {
            $frameSize = FrameSize::fromRequest('letter', null, 'm');

            expect($frameSize->unit)->toBe(FrameSizeUnit::LETTER);
            expect($frameSize->letterValue)->toBe(FrameSizeLetter::M);
        });

        it('creates frame size from cm request', function () {
            $frameSize = FrameSize::fromRequest('cm', 55.0, null);

            expect($frameSize->unit)->toBe(FrameSizeUnit::CM);
            expect($frameSize->numericValue)->toBe(55.0);
        });

        it('creates frame size from inch request', function () {
            $frameSize = FrameSize::fromRequest('inch', 17.0, null);

            expect($frameSize->unit)->toBe(FrameSizeUnit::INCH);
            expect($frameSize->numericValue)->toBe(17.0);
        });

        it('throws exception for invalid parameters', function () {
            expect(fn () => FrameSize::fromRequest('invalid', null, null))
                ->toThrow(\InvalidArgumentException::class, 'Invalid frame size parameters');
        });
    });

    describe('displayValue', function () {
        it('returns letter label for letter sizes', function () {
            $frameSize = FrameSize::fromLetter(FrameSizeLetter::M);

            expect($frameSize->displayValue())->toBe('M');
        });

        it('returns cm value for cm sizes', function () {
            $frameSize = FrameSize::fromCentimeters(55.0);

            expect($frameSize->displayValue())->toBe('55 cm');
        });

        it('returns inch value for inch sizes', function () {
            $frameSize = FrameSize::fromInches(17.0);

            expect($frameSize->displayValue())->toBe('17"');
        });
    });

    describe('toArray', function () {
        it('returns complete array representation for letter size', function () {
            $frameSize = FrameSize::fromLetter(FrameSizeLetter::M);
            $array = $frameSize->toArray();

            expect($array)->toHaveKeys(['unit', 'numeric_value', 'letter_value', 'letter_equivalent', 'display_value']);
            expect($array['unit'])->toBe('letter');
            expect($array['numeric_value'])->toBeNull();
            expect($array['letter_value'])->toBe('m');
            expect($array['letter_equivalent'])->toBe('m');
            expect($array['display_value'])->toBe('M');
        });

        it('returns complete array representation for cm size', function () {
            $frameSize = FrameSize::fromCentimeters(55.0);
            $array = $frameSize->toArray();

            expect($array['unit'])->toBe('cm');
            expect($array['numeric_value'])->toBe(55.0);
            expect($array['letter_value'])->toBeNull();
            expect($array['letter_equivalent'])->toBe('m');
            expect($array['display_value'])->toBe('55 cm');
        });
    });
});
