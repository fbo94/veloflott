<?php

declare(strict_types=1);

namespace Tenant\Application\CreateSite;

use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;
use Tenant\Domain\TenantRepositoryInterface;

final readonly class CreateSiteHandler
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function handle(CreateSiteCommand $command): Site
    {
        // Vérifier que le tenant existe
        $tenant = $this->tenantRepository->findById($command->tenantId);
        if ($tenant === null) {
            throw new \DomainException('Tenant not found');
        }

        // Vérifier l'unicité du slug
        $existingSite = $this->siteRepository->findBySlug($command->tenantId, $command->slug);
        if ($existingSite !== null) {
            throw new \DomainException('A site with this slug already exists for this tenant');
        }

        // Créer le site
        $site = Site::create(
            id: \Ramsey\Uuid\Uuid::uuid4()->toString(),
            tenantId: $command->tenantId,
            name: $command->name,
            slug: $command->slug,
            address: $command->address,
            city: $command->city,
            postalCode: $command->postalCode,
            country: $command->country,
        );

        // Ajouter les informations optionnelles
        if ($command->phone !== null || $command->email !== null) {
            $site->updateInformation(
                name: $command->name,
                address: $command->address,
                city: $command->city,
                postalCode: $command->postalCode,
                country: $command->country,
                phone: $command->phone,
                email: $command->email,
            );
        }

        if ($command->latitude !== null && $command->longitude !== null) {
            $site->setGeolocation($command->latitude, $command->longitude);
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
