<?php

declare(strict_types=1);

namespace Pricing\Application\CreatePricingClass;

use Pricing\Domain\PricingClass;
use Pricing\Domain\PricingClassRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class CreatePricingClassHandler
{
    public function __construct(
        private PricingClassRepositoryInterface $pricingClassRepository,
    ) {}

    public function handle(CreatePricingClassCommand $command): PricingClassDto
    {
        // Vérifier l'unicité du code
        if ($this->pricingClassRepository->existsWithCode($command->code)) {
            throw new \DomainException("Pricing class with code '{$command->code}' already exists");
        }

        // Créer la classe de tarification
        $pricingClass = PricingClass::create(
            id: Uuid::uuid4()->toString(),
            code: $command->code,
            label: $command->label,
            description: $command->description,
            color: $command->color,
            sortOrder: $command->sortOrder,
        );

        // Sauvegarder
        $this->pricingClassRepository->save($pricingClass);

        // Retourner le DTO
        return PricingClassDto::fromDomain($pricingClass);
    }
}
