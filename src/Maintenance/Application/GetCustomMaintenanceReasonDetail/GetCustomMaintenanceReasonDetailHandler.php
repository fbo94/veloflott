<?php

declare(strict_types=1);

namespace Maintenance\Application\GetCustomMaintenanceReasonDetail;

use Maintenance\Application\CreateCustomMaintenanceReason\CustomMaintenanceReasonNotFoundException;
use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;

final readonly class GetCustomMaintenanceReasonDetailHandler
{
    public function __construct(
        private CustomMaintenanceReasonRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws CustomMaintenanceReasonNotFoundException
     */
    public function handle(GetCustomMaintenanceReasonDetailQuery $query): GetCustomMaintenanceReasonDetailResponse
    {
        $reason = $this->repository->findById($query->id);

        if ($reason === null) {
            throw CustomMaintenanceReasonNotFoundException::withId($query->id);
        }

        return GetCustomMaintenanceReasonDetailResponse::fromCustomMaintenanceReason($reason);
    }
}
