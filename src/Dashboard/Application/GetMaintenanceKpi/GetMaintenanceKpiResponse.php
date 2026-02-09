<?php

declare(strict_types=1);

namespace Dashboard\Application\GetMaintenanceKpi;

final readonly class GetMaintenanceKpiResponse
{
    /**
     * @param  array{from: string, to: string, days: int}  $period
     * @param  array<string, int>  $byStatus
     */
    public function __construct(
        public array $period,
        public int $totalCompleted,
        public int $totalInProgress,
        public int $totalTodo,
        public int $total,
        public array $byStatus,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'total_completed' => $this->totalCompleted,
            'total_in_progress' => $this->totalInProgress,
            'total_todo' => $this->totalTodo,
            'total' => $this->total,
            'by_status' => $this->byStatus,
        ];
    }
}
