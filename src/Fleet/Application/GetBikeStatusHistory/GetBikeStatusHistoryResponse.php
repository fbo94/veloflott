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

    public function toArray(): array
    {
        return [
            'data' => array_map(fn ($dto) => $dto->toArray(), $this->history),
        ];
    }
}
