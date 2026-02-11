<?php

declare(strict_types=1);

namespace Tenant\Application\UpdateSite;

final readonly class UpdateSiteCommand
{
    /**
     * @param array<string, array{open: string, close: string}>|null $openingHours
     * @param array<string, mixed>|null $settings
     */
    public function __construct(
        public string $siteId,
        public string $name,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public ?string $country = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?array $openingHours = null,
        public ?array $settings = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {
    }
}
