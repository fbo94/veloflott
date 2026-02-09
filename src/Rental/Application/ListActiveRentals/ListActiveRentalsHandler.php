<?php

declare(strict_types=1);

namespace Rental\Application\ListActiveRentals;

use Rental\Domain\RentalRepositoryInterface;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;

final class ListActiveRentalsHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
    ) {
    }

    public function handle(ListActiveRentalsQuery $query): ListActiveRentalsResponse
    {
        // Si seulement les retards sont demandés
        if ($query->onlyLate) {
            $rentalsModels = $this->findLateRentalsWithFilters($query);
        } else {
            $rentalsModels = $this->findActiveRentalsWithFilters($query);
        }

        $rentalDtos = array_map(
            fn ($model) => ActiveRentalDto::fromEloquentModel($model),
            $rentalsModels
        );

        return new ListActiveRentalsResponse($rentalDtos);
    }

    private function findActiveRentalsWithFilters(ListActiveRentalsQuery $query): array
    {
        $queryBuilder = RentalEloquentModel::with(['customer', 'items.bike.model.brand'])
            ->where('status', 'active')
            ->orderBy('expected_return_date');

        if ($query->customerId !== null) {
            $queryBuilder->where('customer_id', $query->customerId);
        }

        if ($query->bikeId !== null) {
            $queryBuilder->whereHas('items', function ($q) use ($query) {
                $q->where('bike_id', $query->bikeId);
            });
        }

        return $queryBuilder->get()->all();
    }

    private function findLateRentalsWithFilters(ListActiveRentalsQuery $query): array
    {
        $queryBuilder = RentalEloquentModel::with(['customer', 'items.bike.model.brand'])
            ->where('status', 'active')
            ->where('expected_return_date', '<', now())
            ->orderBy('expected_return_date');

        if ($query->customerId !== null) {
            $queryBuilder->where('customer_id', $query->customerId);
        }

        if ($query->bikeId !== null) {
            $queryBuilder->whereHas('items', function ($q) use ($query) {
                $q->where('bike_id', $query->bikeId);
            });
        }

        return $queryBuilder->get()->all();
    }
}
