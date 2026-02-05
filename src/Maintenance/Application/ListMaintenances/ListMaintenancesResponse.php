<?php

declare(strict_types=1);

namespace Maintenance\Application\ListMaintenances;

use Maintenance\Domain\Maintenance;

final readonly class ListMaintenancesResponse
{
    /**
     * @param Maintenance[] $maintenances
     * @param \Illuminate\Support\Collection $bikes
     * @param array<string, int> $countsByStatus
     */
    public function __construct(
        public array $maintenances,
        public \Illuminate\Support\Collection $bikes,
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
                    'bike' => $this->getBikeData($m->bikeId()),
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
                    'photos' => $m->photos(),
                    'created_at' => $m->createdAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $m->updatedAt()->format('Y-m-d H:i:s'),
                ],
                $this->maintenances
            ),
            'counts_by_status' => $this->countsByStatus,
            'total' => $this->total,
        ];
    }

    private function getBikeData(string $bikeId): ?array
    {
        $bike = $this->bikes->get($bikeId);

        if ($bike === null) {
            return null;
        }

        return [
            'id' => $bike->id,
            'internal_number' => $bike->internal_number,
            'brand' => $bike->model->brand->name,
            'model' => $bike->model->name,
            'category_id' => $bike->category_id,
            'category_name' => $bike->category->name,
        ];
    }
}
