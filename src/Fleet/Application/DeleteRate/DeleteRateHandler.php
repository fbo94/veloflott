<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteRate;

use Fleet\Domain\RateRepositoryInterface;

final class DeleteRateHandler
{
    public function __construct(
        private readonly RateRepositoryInterface $rates,
    ) {}

    public function handle(DeleteRateCommand $command): void
    {
        // Vérifier que le tarif existe
        $rate = $this->rates->findById($command->id);
        if ($rate === null) {
            throw new RateNotFoundException($command->id);
        }

        // Supprimer
        $this->rates->delete($command->id);
    }
}
