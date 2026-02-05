<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBike;

use Fleet\Domain\Bike;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\FrameSize;
use Fleet\Domain\FrameSizeUnit;
use Fleet\Domain\ModelRepositoryInterface;
use Illuminate\Support\Str;

final class CreateBikeHandler
{
    public function __construct(
        private readonly BikeRepositoryInterface $bikes,
        private readonly ModelRepositoryInterface $models,
        private readonly BrandRepositoryInterface $brands,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function handle(CreateBikeCommand $command): CreateBikeResponse
    {
        // Vérifier que le numéro interne est unique
        if ($this->bikes->findByInternalNumber($command->internalNumber) !== null) {
            throw new BikeInternalNumberAlreadyExistsException($command->internalNumber);
        }

        // Vérifier que le modèle existe
        $model = $this->models->findById($command->modelId);
        if ($model === null) {
            throw new ModelNotFoundException($command->modelId);
        }

        // Récupérer la marque du modèle
        $brand = $this->brands->findById($model->brandId());
        if ($brand === null) {
            throw new \DomainException("La marque du modèle '{$command->modelId}' n'existe pas.");
        }

        // Vérifier que la catégorie existe
        if ($this->categories->findById($command->categoryId) === null) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        // Créer le FrameSize selon l'unité
        $frameSize = match ($command->frameSizeUnit) {
            FrameSizeUnit::LETTER => FrameSize::fromLetter($command->frameSizeLetter),
            FrameSizeUnit::CM => FrameSize::fromCentimeters($command->frameSizeNumeric),
            FrameSizeUnit::INCH => FrameSize::fromInches($command->frameSizeNumeric),
        };

        // Créer le vélo
        $bike = new Bike(
            id: Str::uuid()->toString(),
            qrCodeUuid: Str::uuid()->toString(),
            internalNumber: $command->internalNumber,
            modelId: $command->modelId,
            categoryId: $command->categoryId,
            frameSize: $frameSize,
            status: BikeStatus::AVAILABLE,
            year: $command->year,
            serialNumber: $command->serialNumber,
            color: $command->color,
            wheelSize: $command->wheelSize,
            frontSuspension: $command->frontSuspension,
            rearSuspension: $command->rearSuspension,
            brakeType: $command->brakeType,
            purchasePrice: $command->purchasePrice,
            purchaseDate: $command->purchaseDate,
            notes: $command->notes,
            photos: $command->photos,
        );

        $this->bikes->save($bike);

        return CreateBikeResponse::fromBike($bike, $model, $brand);
    }
}
