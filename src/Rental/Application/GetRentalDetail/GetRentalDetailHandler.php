<?php

declare(strict_types=1);

namespace Rental\Application\GetRentalDetail;

use Customer\Domain\CustomerRepositoryInterface;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalRepositoryInterface;
use RuntimeException;

final class GetRentalDetailHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly CustomerRepositoryInterface $customers,
        private readonly BikeRepositoryInterface $bikes,
    ) {}

    public function handle(GetRentalDetailQuery $query): GetRentalDetailResponse
    {
        $rental = $this->rentals->findById($query->rentalId);

        if ($rental === null) {
            throw new RentalNotFoundException($query->rentalId);
        }

        // Calculer le nombre de jours
        $numberOfDays = $rental->duration()->days();
        if ($rental->duration() === RentalDuration::CUSTOM) {
            $numberOfDays = ($rental->expectedReturnDate()->getTimestamp() - $rental->startDate()->getTimestamp()) / 86400;
        }

        // Récupérer les infos du client
        $customer = $this->customers->findById($rental->customerId());
        if ($customer === null) {
            throw new RuntimeException("Customer not found for rental {$query->rentalId}");
        }

        $customerData = [
            'first_name' => $customer->firstName(),
            'last_name' => $customer->lastName(),
            'email' => $customer->email(),
            'phone' => $customer->phone(),
        ];

        // Mapper les items avec détails des vélos
        $items = [];
        foreach ($rental->items() as $item) {
            $bike = $this->bikes->findById($item->bikeId());
            if ($bike === null) {
                throw new RuntimeException("Bike not found: {$item->bikeId()}");
            }

            // Pour obtenir brand et model, on doit les récupérer via le repository
            // Simplifions en utilisant le BikeEloquentModel directement
            $bikeModel = BikeEloquentModel::with(['model.brand', 'category'])
                ->find($bike->id());

            $bikeDetails = [
                'brand' => $bikeModel->model->brand->name,
                'model' => $bikeModel->model->name,
                'category_id' => $bikeModel->category->id,
                'category_name' => $bikeModel->category->name,
                'internal_number' => $bike->internalNumber(),
                'serial_number' => $bike->serialNumber(),
                'purchase_price' => $bike->purchasePrice(),
            ];

            $items[] = RentalItemDto::fromRentalItem($item, $bikeDetails, $numberOfDays);
        }

        // Mapper les équipements
        $equipments = array_map(
            fn ($equipment) => RentalEquipmentDto::fromRentalEquipment($equipment),
            $rental->equipments()
        );

        return GetRentalDetailResponse::fromRental($rental, $customerData, $items, $equipments, $numberOfDays);
    }
}
