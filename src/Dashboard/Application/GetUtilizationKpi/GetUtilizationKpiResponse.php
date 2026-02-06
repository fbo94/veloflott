<?php

declare(strict_types=1);

namespace Dashboard\Application\GetUtilizationKpi;

final readonly class GetUtilizationKpiResponse
{
    /**
     * @param  array{from: string, to: string, days: int}  $period
     */
    public function __construct(
        public array $period,
        public float $utilizationRate,
        public int $rentedBikes,
        public int $availableBikes,
        public int $totalRentableBikes,
        public float $avgRentalDurationHours,
        public int $rentalCountInPeriod,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'utilization_rate' => $this->utilizationRate,
            'rented_bikes' => $this->rentedBikes,
            'available_bikes' => $this->availableBikes,
            'total_rentable_bikes' => $this->totalRentableBikes,
            'avg_rental_duration_hours' => $this->avgRentalDurationHours,
            'rental_count_in_period' => $this->rentalCountInPeriod,
        ];
    }
}
