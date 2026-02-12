<?php

declare(strict_types=1);

namespace Maintenance\Application\GetCustomMaintenanceReasonDetail;

use Maintenance\Domain\CustomMaintenanceReason;

final readonly class GetCustomMaintenanceReasonDetailResponse
{
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $code,
        public string $label,
        public ?string $description,
        public string $category,
        public string $categoryLabel,
        public bool $isActive,
        public int $sortOrder,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromCustomMaintenanceReason(CustomMaintenanceReason $reason): self
    {
        return new self(
            id: $reason->id(),
            tenantId: $reason->tenantId(),
            code: $reason->code(),
            label: $reason->label(),
            description: $reason->description(),
            category: $reason->category()->value,
            categoryLabel: $reason->category()->label(),
            isActive: $reason->isActive(),
            sortOrder: $reason->sortOrder(),
            createdAt: $reason->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $reason->updatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'code' => $this->code,
            'label' => $this->label,
            'description' => $this->description,
            'category' => $this->category,
            'category_label' => $this->categoryLabel,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
