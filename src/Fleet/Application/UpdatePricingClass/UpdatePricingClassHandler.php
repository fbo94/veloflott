<?php

declare(strict_types=1);

namespace Fleet\Application\UpdatePricingClass;

use Fleet\Domain\PricingClassRepositoryInterface;

final readonly class UpdatePricingClassHandler
{
    public function __construct(
        private PricingClassRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdatePricingClassCommand $command): UpdatePricingClassResponse
    {
        $pricingClass = $this->repository->findById($command->id);

        if ($pricingClass === null) {
            throw new \DomainException("PricingClass with id {$command->id} not found");
        }

        $pricingClass->update(
            label: $command->label,
            description: $command->description,
            color: $command->color,
            sortOrder: $command->sortOrder,
        );

        if ($command->isActive) {
            $pricingClass->activate();
        } else {
            $pricingClass->deactivate();
        }

        $this->repository->save($pricingClass);

        return UpdatePricingClassResponse::fromDomain($pricingClass);
    }
}
