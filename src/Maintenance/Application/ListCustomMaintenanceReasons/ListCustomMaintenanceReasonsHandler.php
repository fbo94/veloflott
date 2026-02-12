<?php

declare(strict_types=1);

namespace Maintenance\Application\ListCustomMaintenanceReasons;

use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;
use Maintenance\Domain\MaintenanceCategory;

final readonly class ListCustomMaintenanceReasonsHandler
{
    public function __construct(
        private CustomMaintenanceReasonRepositoryInterface $repository,
    ) {
    }

    public function handle(ListCustomMaintenanceReasonsQuery $query): ListCustomMaintenanceReasonsResponse
    {
        // Convertir la catégorie si fournie
        $category = $query->category !== null
            ? MaintenanceCategory::from($query->category)
            : null;

        // Récupérer toutes les raisons personnalisées
        $reasons = $this->repository->findAll($category, $query->isActive);

        // Mapper les raisons vers des tableaux
        $reasonsData = array_map(
            fn ($reason) => [
                'id' => $reason->id(),
                'code' => $reason->code(),
                'label' => $reason->label(),
                'description' => $reason->description(),
                'category' => $reason->category()->value,
                'category_label' => $reason->category()->label(),
                'is_active' => $reason->isActive(),
                'sort_order' => $reason->sortOrder(),
                'created_at' => $reason->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $reason->updatedAt()->format('Y-m-d H:i:s'),
            ],
            $reasons
        );

        return new ListCustomMaintenanceReasonsResponse($reasonsData);
    }
}
