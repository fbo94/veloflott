<?php

declare(strict_types=1);

namespace Tenant\Application\UpdateSite;

use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;

final readonly class UpdateSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {
    }

    public function handle(UpdateSiteCommand $command): Site
    {
        $site = $this->siteRepository->findById($command->siteId);

        if ($site === null) {
            throw new \DomainException('Site not found');
        }

        $site->updateInformation(
            name: $command->name,
            address: $command->address,
            city: $command->city,
            postalCode: $command->postalCode,
            country: $command->country,
            phone: $command->phone,
            email: $command->email,
        );

        if ($command->latitude !== null && $command->longitude !== null) {
            $site->setGeolocation($command->latitude, $command->longitude);
        } elseif ($command->latitude === null && $command->longitude === null) {
            $site->clearGeolocation();
        }

        if ($command->openingHours !== null) {
            $site->setOpeningHours($command->openingHours);
        }

        if ($command->settings !== null) {
            $site->updateSettings($command->settings);
        }

        $this->siteRepository->save($site);

        return $site;
    }
}
