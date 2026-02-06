<?php

declare(strict_types=1);

namespace Fleet\Application\ChangeBikeStatus;

final readonly class ChangeBikeStatusCommand
{
    public function __construct(
        public string $bikeId,
        public string $status,
        public ?string $unavailabilityReason = null,
        public ?string $unavailabilityComment = null,
    ) {}
}
