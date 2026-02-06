<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeStatusHistory;

final readonly class GetBikeStatusHistoryResponse
{
    /**
     * @param BikeStatusHistoryDto[] $history
     */
    public function __construct(
        public array $history,
    ) {
    }

    /**
     * @return array{data: array<int, array{id: string, old_status: string, old_status_label: string, new_status: string, new_status_label: string, unavailability_reason: string|null, unavailability_reason_label: string|null, unavailability_comment: string|null, changed_at: string}>}
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(fn ($dto) => $dto->toArray(), $this->history),
        ];
    }
}
