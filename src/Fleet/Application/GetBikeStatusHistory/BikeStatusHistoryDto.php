<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeStatusHistory;

final readonly class BikeStatusHistoryDto
{
    public function __construct(
        public string $id,
        public string $oldStatus,
        public string $oldStatusLabel,
        public string $newStatus,
        public string $newStatusLabel,
        public ?string $unavailabilityReason,
        public ?string $unavailabilityReasonLabel,
        public ?string $unavailabilityComment,
        public string $changedAt,
    ) {}

    /**
     * @return array{id: string, old_status: string, old_status_label: string, new_status: string, new_status_label: string, unavailability_reason: string|null, unavailability_reason_label: string|null, unavailability_comment: string|null, changed_at: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'old_status' => $this->oldStatus,
            'old_status_label' => $this->oldStatusLabel,
            'new_status' => $this->newStatus,
            'new_status_label' => $this->newStatusLabel,
            'unavailability_reason' => $this->unavailabilityReason,
            'unavailability_reason_label' => $this->unavailabilityReasonLabel,
            'unavailability_comment' => $this->unavailabilityComment,
            'changed_at' => $this->changedAt,
        ];
    }
}
