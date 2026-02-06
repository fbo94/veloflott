<?php

declare(strict_types=1);

namespace Fleet\Application\SetBikeRate;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\Rate;
use Fleet\Domain\RateRepositoryInterface;
use Illuminate\Support\Str;

final class SetBikeRateHandler
{
    public function __construct(
        private readonly RateRepositoryInterface $rates,
        private readonly BikeRepositoryInterface $bikes,
    ) {}

    public function handle(SetBikeRateCommand $command): SetBikeRateResponse
    {
        // Vérifier que le vélo existe
        $bike = $this->bikes->findById($command->bikeId);
        if ($bike === null) {
            throw new \DomainException("Le vélo '{$command->bikeId}' n'existe pas.");
        }

        // Vérifier s'il existe déjà un tarif pour ce vélo
        $existingRate = $this->rates->findByBikeId($command->bikeId);

        if ($existingRate !== null) {
            // Mettre à jour le tarif existant
            $updatedRate = $existingRate->updatePrices(
                halfDayPrice: $command->halfDayPrice,
                dayPrice: $command->dayPrice,
                weekendPrice: $command->weekendPrice,
                weekPrice: $command->weekPrice,
            );
            $this->rates->save($updatedRate);

            return new SetBikeRateResponse(
                rateId: $updatedRate->id(),
                bikeId: $command->bikeId,
                message: 'Tarif mis à jour avec succès',
            );
        }

        // Créer un nouveau tarif
        $rate = Rate::forBike(
            id: Str::uuid()->toString(),
            bikeId: $command->bikeId,
            dayPrice: $command->dayPrice,
            halfDayPrice: $command->halfDayPrice,
            weekendPrice: $command->weekendPrice,
            weekPrice: $command->weekPrice,
        );

        $this->rates->save($rate);

        return new SetBikeRateResponse(
            rateId: $rate->id(),
            bikeId: $command->bikeId,
            message: 'Tarif créé avec succès',
        );
    }
}
