<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use DateTimeImmutable;

final readonly class UnavailabilitySlot
{
    public function __construct(
        public string $type,              // 'rental' or 'maintenance'
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public ?string $referenceId,      // rental_id or maintenance_id
        public ?string $details = null,   // Additional info (e.g., rental status, maintenance reason)
    ) {
    }

    public static function fromRental(
        string $rentalId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        string $status,
    ): self {
        return new self(
            type: 'rental',
            startDate: $startDate,
            endDate: $endDate,
            referenceId: $rentalId,
            details: $status,
        );
    }

    public static function fromMaintenance(
        string $maintenanceId,
        DateTimeImmutable $scheduledAt,
        ?DateTimeImmutable $completedAt,
        string $reason,
    ): self {
        // For maintenances, if not completed, assume it blocks until scheduled date + 1 day
        $endDate = $completedAt ?? $scheduledAt->modify('+1 day');

        return new self(
            type: 'maintenance',
            startDate: $scheduledAt,
            endDate: $endDate,
            referenceId: $maintenanceId,
            details: $reason,
        );
    }

    /**
     * Check if this slot overlaps with a given period
     */
    public function overlaps(DateTimeImmutable $startDate, DateTimeImmutable $endDate): bool
    {
        return $this->startDate < $endDate && $this->endDate > $startDate;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'start_date' => $this->startDate->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate->format('Y-m-d H:i:s'),
            'reference_id' => $this->referenceId,
            'details' => $this->details,
        ];
    }
}
