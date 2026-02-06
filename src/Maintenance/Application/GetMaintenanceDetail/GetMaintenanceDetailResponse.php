<?php

declare(strict_types=1);

namespace Maintenance\Application\GetMaintenanceDetail;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Maintenance\Domain\Maintenance;

final readonly class GetMaintenanceDetailResponse
{
    /**
     * @param  array<int, string>  $photos
     */
    public function __construct(
        private string $id,
        private string $bikeId,
        private string $type,
        private string $reason,
        private string $priority,
        private string $status,
        private ?string $description,
        private string $scheduledAt,
        private ?string $startedAt,
        private ?string $completedAt,
        private ?string $workDescription,
        private ?string $partsReplaced,
        private ?float $cost,
        private array $photos,
        private string $createdAt,
        private string $updatedAt,
        private ?array $bike,
    ) {}

    public static function fromMaintenance(Maintenance $maintenance, ?BikeEloquentModel $bikeModel): self
    {
        return new self(
            id: $maintenance->id(),
            bikeId: $maintenance->bikeId(),
            type: $maintenance->type()->value,
            reason: $maintenance->reason()->value,
            priority: $maintenance->priority()->value,
            status: $maintenance->status()->value,
            description: $maintenance->description(),
            scheduledAt: $maintenance->scheduledAt()->format('Y-m-d H:i:s'),
            startedAt: $maintenance->startedAt()?->format('Y-m-d H:i:s'),
            completedAt: $maintenance->completedAt()?->format('Y-m-d H:i:s'),
            workDescription: $maintenance->workDescription(),
            partsReplaced: $maintenance->partsReplaced(),
            cost: $maintenance->cost() !== null ? $maintenance->cost() / 100 : null,
            photos: $maintenance->photos(),
            createdAt: $maintenance->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $maintenance->updatedAt()->format('Y-m-d H:i:s'),
            bike: $bikeModel !== null ? self::formatBike($bikeModel) : null,
        );
    }

    private static function formatBike(BikeEloquentModel $bike): array
    {
        return [
            'id' => $bike->id,
            'internal_number' => $bike->internal_number,
            'brand' => $bike->model->brand->name,
            'model' => $bike->model->name,
            'category_id' => $bike->category_id,
            'category_name' => $bike->category->name,
            'status' => $bike->status,
            'purchase_price' => $bike->purchase_price,
            'purchase_date' => $bike->purchase_date?->format('Y-m-d'),
            'frame_size_cm' => $bike->frame_size_cm,
            'frame_size_inches' => $bike->frame_size_inches,
            'frame_size_letter_equivalent' => $bike->frame_size_letter_equivalent,
            'color' => $bike->color,
            'serial_number' => $bike->serial_number,
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bike_id' => $this->bikeId,
            'type' => $this->type,
            'reason' => $this->reason,
            'priority' => $this->priority,
            'status' => $this->status,
            'description' => $this->description,
            'scheduled_at' => $this->scheduledAt,
            'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt,
            'work_description' => $this->workDescription,
            'parts_replaced' => $this->partsReplaced,
            'cost' => $this->cost,
            'photos' => $this->photos,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'bike' => $this->bike,
        ];
    }
}
