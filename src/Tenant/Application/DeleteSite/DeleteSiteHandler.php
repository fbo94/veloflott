<?php

declare(strict_types=1);

namespace Tenant\Application\DeleteSite;

use Tenant\Domain\SiteRepositoryInterface;

final readonly class DeleteSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {
    }

    public function handle(DeleteSiteCommand $command): void
    {
        $site = $this->siteRepository->findById($command->siteId);

        if ($site === null) {
            throw new \DomainException('Site not found');
        }

        // On pourrait ajouter des vérifications ici (pas de locations actives, etc.)
        if ($site->isActive()) {
            throw new \DomainException('Cannot delete an active site. Please close or suspend it first.');
        }

        $this->siteRepository->delete($command->siteId);
    }
}
