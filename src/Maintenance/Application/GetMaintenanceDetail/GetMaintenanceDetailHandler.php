<?php

declare(strict_types=1);

namespace Maintenance\Application\GetMaintenanceDetail;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Maintenance\Domain\MaintenanceRepositoryInterface;

final readonly class GetMaintenanceDetailHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {}

    /**
     * @throws MaintenanceNotFoundException
     */
    public function handle(GetMaintenanceDetailQuery $query): GetMaintenanceDetailResponse
    {
        $maintenance = $this->maintenanceRepository->findById($query->maintenanceId);

        if ($maintenance === null) {
            throw new MaintenanceNotFoundException($query->maintenanceId);
        }

        // Eager load bike avec relations
        $bikeModel = BikeEloquentModel::with(['model.brand', 'category'])
            ->find($maintenance->bikeId());

        return GetMaintenanceDetailResponse::fromMaintenance($maintenance, $bikeModel);
    }
}
