<?php

declare(strict_types=1);

namespace Maintenance\Application\GetBikeMaintenanceHistory;

use Maintenance\Domain\Maintenance;

final readonly class GetBikeMaintenanceHistoryResponse
{
    /**
     * @param Maintenance[] $maintenances
     */
    public function __construct(
        public string $bikeId,
        public string $bikeInternalNumber,
        public array $maintenances,
        public int $total,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'bike_id' => $this->bikeId,
            'bike_internal_number' => $this->bikeInternalNumber,
            'maintenances' => array_map(
                fn (Maintenance $m) => [
                    'id' => $m->id(),
                    'type' => $m->type()->value,
                    'type_label' => $m->type()->label(),
                    'reason' => $m->reason()->value,
                    'reason_label' => $m->reason()->label(),
                    'category' => $m->reason()->category()->value,
                    'category_label' => $m->reason()->category()->label(),
                    'priority' => $m->priority()->value,
                    'status' => $m->status()->value,
                    'description' => $m->description(),
                    'scheduled_at' => $m->scheduledAt()->format('Y-m-d H:i:s'),
                    'started_at' => $m->startedAt()?->format('Y-m-d H:i:s'),
                    'completed_at' => $m->completedAt()?->format('Y-m-d H:i:s'),
                    'work_description' => $m->workDescription(),
                    'parts_replaced' => $m->partsReplaced(),
                    'cost' => $m->cost(),
                    'created_at' => $m->createdAt()->format('Y-m-d H:i:s'),
                ],
                $this->maintenances
            ),
            'total' => $this->total,
        ];
    }
}
