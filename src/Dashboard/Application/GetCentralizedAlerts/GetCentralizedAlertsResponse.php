<?php

declare(strict_types=1);

namespace Dashboard\Application\GetCentralizedAlerts;

final readonly class GetCentralizedAlertsResponse
{
    /**
     * @param  array<int, array<string, mixed>>  $alerts
     * @param  array{high: int, medium: int, low: int}  $countsBySeverity
     */
    public function __construct(
        public array $alerts,
        public array $countsBySeverity,
        public int $total,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'alerts' => $this->alerts,
            'counts_by_severity' => $this->countsBySeverity,
            'total' => $this->total,
        ];
    }
}
