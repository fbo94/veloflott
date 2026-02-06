<?php

declare(strict_types=1);

namespace Fleet\Application\RetireBike;

use Fleet\Application\UpdateBike\BikeNotFoundException;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\RetirementReason;

final readonly class RetireBikeHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
    ) {}

    public function handle(RetireBikeCommand $command): RetireBikeResponse
    {
        $bike = $this->bikeRepository->findById($command->bikeId);

        if ($bike === null) {
            throw new BikeNotFoundException($command->bikeId);
        }

        $reason = RetirementReason::from($command->reason);

        try {
            $bike->retire($reason, $command->comment);
            $this->bikeRepository->save($bike);

            return new RetireBikeResponse(id: $bike->id());
        } catch (\DomainException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }
}
