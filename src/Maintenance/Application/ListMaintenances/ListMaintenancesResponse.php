<?php

declare(strict_types=1);

namespace Maintenance\Application\ListMaintenances;

use Maintenance\Domain\Maintenance;

final readonly class ListMaintenancesResponse
{
    /**
     * @param Maintenance[] $maintenances
     * @param array<string, int> $countsByStatus
     */
    public function __construct(
        public array $maintenances,
        public array $countsByStatus,
        public int $total,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'maintenances' => array_map(
                fn (Maintenance $m) => [
                    'id' => $m->id(),
                    'bike_id' => $m->bikeId(),
                    'type' => $m->type()->value,
                    'type_label' => $m->type()->label(),
                    'reason' => $m->reason()->value,
                    'reason_label' => $m->reason()->label(),
                    'category' => $m->reason()->category()->value,
                    'category_label' => $m->reason()->category()->label(),
                    'priority' => $m->priority()->value,
                    'is_urgent' => $m->priority()->isUrgent(),
                    'status' => $m->status()->value,
                    'description' => $m->description(),
                    'scheduled_at' => $m->scheduledAt()->format('Y-m-d H:i:s'),
                    'started_at' => $m->startedAt()?->format('Y-m-d H:i:s'),
                    'completed_at' => $m->completedAt()?->format('Y-m-d H:i:s'),
                    'work_description' => $m->workDescription(),
                    'parts_replaced' => $m->partsReplaced(),
                    'cost' => $m->cost(),
                    'created_at' => $m->createdAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $m->updatedAt()->format('Y-m-d H:i:s'),
                ],
                $this->maintenances
            ),
            'counts_by_status' => $this->countsByStatus,
            'total' => $this->total,
        ];
    }
}
