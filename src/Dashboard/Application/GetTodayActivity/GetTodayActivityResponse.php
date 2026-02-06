<?php

declare(strict_types=1);

namespace Dashboard\Application\GetTodayActivity;

final readonly class GetTodayActivityResponse
{
    /**
     * @param  array<int, array{id: string, customer_id: string, start_date: string, expected_return_date: string, status: string, total_amount: int}>  $recentRentals
     */
    public function __construct(
        public string $date,
        public int $rentalsStartedToday,
        public int $scheduledReturnsToday,
        public int $lateReturns,
        public array $recentRentals,
        public int $maintenancesScheduledToday,
        public int $maintenancesCompletedToday,
        public int $urgentPendingMaintenances,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'rentals' => [
                'started_today' => $this->rentalsStartedToday,
                'scheduled_returns_today' => $this->scheduledReturnsToday,
                'late_returns' => $this->lateReturns,
                'recent_rentals' => $this->recentRentals,
            ],
            'maintenances' => [
                'scheduled_today' => $this->maintenancesScheduledToday,
                'completed_today' => $this->maintenancesCompletedToday,
                'urgent_pending' => $this->urgentPendingMaintenances,
            ],
        ];
    }
}
