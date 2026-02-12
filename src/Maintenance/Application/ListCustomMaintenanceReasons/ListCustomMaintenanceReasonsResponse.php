<?php

declare(strict_types=1);

namespace Maintenance\Application\ListCustomMaintenanceReasons;

final readonly class ListCustomMaintenanceReasonsResponse
{
    /**
     * @param  array<int, array{id: string, code: string, label: string, description: ?string, category: string, category_label: string, is_active: bool, sort_order: int, created_at: string, updated_at: string}>  $reasons
     */
    public function __construct(
        public array $reasons,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => $this->reasons,
            'total' => count($this->reasons),
        ];
    }
}
