<?php

declare(strict_types=1);

namespace Rental\Application\ListRentals;

use Customer\Domain\CustomerRepositoryInterface;
use Fleet\Domain\BikeRepositoryInterface;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;

final class ListRentalsHandler
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly BikeRepositoryInterface $bikes,
    ) {
    }

    public function handle(ListRentalsQuery $query): ListRentalsResponse
    {
        $queryBuilder = RentalEloquentModel::with(['customer', 'items.bike.model.brand'])
            ->orderBy('created_at', 'desc');

        // Filtrer par client si spécifié
        if ($query->customerId !== null) {
            $queryBuilder->where('customer_id', $query->customerId);
        }

        // Filtrer par statut si spécifié
        if ($query->status !== null) {
            $queryBuilder->where('status', $query->status);
        }

        // Filtrer par période si spécifiée
        if ($query->startDate !== null) {
            $queryBuilder->where('start_date', '>=', $query->startDate);
        }

        if ($query->endDate !== null) {
            $queryBuilder->where('start_date', '<=', $query->endDate);
        }

        // Pagination
        $paginator = $queryBuilder->paginate(
            perPage: $query->perPage,
            page: $query->page
        );

        $rentalDtos = $paginator->items();
        $rentalDtos = array_map(function ($model) {
            $customerName = "{$model->customer->first_name} {$model->customer->last_name}";

            $bikes = $model->items->map(function ($item) {
                $bike = $item->bike;
                return "{$bike->model->brand->name} {$bike->model->name} ({$bike->internal_number})";
            })->all();

            // Convertir le model Eloquent en domaine
            $rental = app(\Rental\Domain\RentalRepositoryInterface::class)->findById($model->id);

            return RentalDto::fromRental($rental, $customerName, $bikes);
        }, $rentalDtos);

        return new ListRentalsResponse(
            rentals: $rentalDtos,
            total: $paginator->total(),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
