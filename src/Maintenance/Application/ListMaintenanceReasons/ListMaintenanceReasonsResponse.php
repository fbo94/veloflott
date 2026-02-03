<?php

declare(strict_types=1);

namespace Maintenance\Application\ListMaintenanceReasons;

final readonly class ListMaintenanceReasonsResponse
{
    /**
     * @param array<int, array{category: string, category_label: string, reasons: array<int, array{value: string, label: string}>}> $categoriesWithReasons
     */
    public function __construct(
        public array $categoriesWithReasons,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'categories' => $this->categoriesWithReasons,
        ];
    }
}
