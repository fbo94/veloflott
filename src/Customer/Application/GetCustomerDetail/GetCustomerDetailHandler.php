<?php

declare(strict_types=1);

namespace Customer\Application\GetCustomerDetail;

use Customer\Domain\CustomerRepositoryInterface;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;

final readonly class GetCustomerDetailHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
    ) {}

    public function handle(GetCustomerDetailQuery $query): CustomerDetailDto
    {
        $customer = $this->customers->findById($query->customerId);
        if ($customer === null) {
            throw new \DomainException("Customer with ID {$query->customerId} not found");
        }

        // Récupérer l'historique des locations
        $rentals = RentalEloquentModel::where('customer_id', $query->customerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $rentalHistory = $rentals->map(function ($rental) {
            return [
                'id' => $rental->id,
                'start_date' => $rental->start_date,
                'expected_return_date' => $rental->expected_return_date,
                'actual_return_date' => $rental->actual_return_date,
                'status' => $rental->status,
                'total_amount' => $rental->total_amount,
                'deposit_amount' => $rental->deposit_amount,
            ];
        })->toArray();

        // Calculer les statistiques
        $totalRentals = $rentals->count();
        $completedRentals = $rentals->where('status', 'completed');
        $totalSpent = $completedRentals->sum('total_amount');

        return new CustomerDetailDto(
            id: $customer->id(),
            firstName: $customer->firstName(),
            lastName: $customer->lastName(),
            email: $customer->email(),
            phone: $customer->phone(),
            identityDocumentType: $customer->identityDocumentType(),
            identityDocumentNumber: $customer->identityDocumentNumber(),
            height: $customer->height(),
            weight: $customer->weight(),
            address: $customer->address(),
            notes: $customer->notes(),
            isRisky: $customer->isRisky(),
            createdAt: $customer->createdAt(),
            updatedAt: $customer->updatedAt(),
            rentalHistory: $rentalHistory,
            totalRentals: $totalRentals,
            totalSpent: $totalSpent,
        );
    }
}
