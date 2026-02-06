<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateRate;

use Fleet\Domain\RateRepositoryInterface;

final class UpdateRateHandler
{
    public function __construct(
        private readonly RateRepositoryInterface $rates,
    ) {}

    public function handle(UpdateRateCommand $command): UpdateRateResponse
    {
        // Vérifier que le tarif existe
        $rate = $this->rates->findById($command->id);
        if ($rate === null) {
            throw new RateNotFoundException($command->id);
        }

        // Mettre à jour
        $rate->updatePrice($command->duration, $command->price);

        $this->rates->save($rate);

        return UpdateRateResponse::fromRate($rate);
    }
}
