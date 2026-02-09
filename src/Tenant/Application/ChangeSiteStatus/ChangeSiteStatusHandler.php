<?php

declare(strict_types=1);

namespace Tenant\Application\ChangeSiteStatus;

use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;
use Tenant\Domain\SiteStatus;

final readonly class ChangeSiteStatusHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function handle(ChangeSiteStatusCommand $command): Site
    {
        $site = $this->siteRepository->findById($command->siteId);

        if ($site === null) {
            throw new \DomainException('Site not found');
        }

        match ($command->status) {
            SiteStatus::ACTIVE => $site->activate(),
            SiteStatus::SUSPENDED => $site->suspend(),
            SiteStatus::CLOSED => $site->close(),
        };

        $this->siteRepository->save($site);

        return $site;
    }
}
