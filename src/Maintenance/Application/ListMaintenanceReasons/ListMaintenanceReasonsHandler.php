<?php

declare(strict_types=1);

namespace Maintenance\Application\ListMaintenanceReasons;

use Maintenance\Domain\MaintenanceCategory;
use Maintenance\Domain\MaintenanceReason;

final readonly class ListMaintenanceReasonsHandler
{
    public function handle(): ListMaintenanceReasonsResponse
    {
        $categoriesWithReasons = [];

        foreach (MaintenanceCategory::cases() as $category) {
            $reasons = MaintenanceReason::byCategory($category);

            $categoriesWithReasons[] = [
                'category' => $category->value,
                'category_label' => $category->label(),
                'reasons' => array_map(
                    fn(MaintenanceReason $reason) => [
                        'value' => $reason->value,
                        'label' => $reason->label(),
                    ],
                    $reasons
                ),
            ];
        }

        return new ListMaintenanceReasonsResponse($categoriesWithReasons);
    }
}
