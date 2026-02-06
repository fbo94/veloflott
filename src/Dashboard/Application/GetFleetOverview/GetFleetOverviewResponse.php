<?php

declare(strict_types=1);

namespace Dashboard\Application\GetFleetOverview;

final readonly class GetFleetOverviewResponse
{
    /**
     * @param  array{total_bikes: int, active_bikes: int, average_age_years: float, by_status: array<string, int>}  $fleetSummary
     * @param  array{active_rentals: int}  $rentalsSummary
     * @param  array{by_status: array<string, int>, urgent_pending: int}  $maintenanceSummary
     * @param  array{total_customers: int, with_active_rental: int}  $customersSummary
     */
    public function __construct(
        public array $fleetSummary,
        public array $rentalsSummary,
        public array $maintenanceSummary,
        public array $customersSummary,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'fleet_summary' => $this->fleetSummary,
            'rentals_summary' => $this->rentalsSummary,
            'maintenance_summary' => $this->maintenanceSummary,
            'customers_summary' => $this->customersSummary,
        ];
    }
}
