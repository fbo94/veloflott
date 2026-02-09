<?php

declare(strict_types=1);

namespace Rental\Application\Services;

final readonly class AvailabilityResult
{
    /**
     * @param UnavailabilitySlot[] $conflictingSlots
     */
    public function __construct(
        public bool $isAvailable,
        public array $conflictingSlots = [],
        public ?string $reason = null,
    ) {
    }

    public static function available(): self
    {
        return new self(isAvailable: true);
    }

    public static function unavailable(string $reason, array $conflictingSlots = []): self
    {
        return new self(
            isAvailable: false,
            conflictingSlots: $conflictingSlots,
            reason: $reason,
        );
    }
}
