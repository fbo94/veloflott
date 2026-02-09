<?php

declare(strict_types=1);

namespace Dashboard\Application\GetRevenueKpi;

final readonly class GetRevenueKpiResponse
{
    /**
     * @param  array{from: string, to: string, days: int}  $period
     */
    public function __construct(
        public array $period,
        public int $totalRevenueCents,
        public string $totalRevenueFormatted,
        public int $revpavCents,
        public int $avgRevenuePerRentalCents,
        public int $rentalCount,
        public int $activeBikes,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'total_revenue_cents' => $this->totalRevenueCents,
            'total_revenue_formatted' => $this->totalRevenueFormatted,
            'revpav_cents' => $this->revpavCents,
            'avg_revenue_per_rental_cents' => $this->avgRevenuePerRentalCents,
            'rental_count' => $this->rentalCount,
            'active_bikes' => $this->activeBikes,
        ];
    }
}
