<?php

declare(strict_types=1);

namespace Fleet\Application\ListModels;

use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Domain\ModelRepositoryInterface;

final class ListModelsHandler
{
    public function __construct(
        private readonly ModelRepositoryInterface $models,
        private readonly BrandRepositoryInterface $brands,
    ) {}

    public function handle(ListModelsQuery $query): ListModelsResponse
    {
        // Récupérer les modèles selon le filtre
        $models = $query->brandId !== null
            ? $this->models->findByBrandId($query->brandId)
            : $this->models->findAll();

        // Indexer les marques pour enrichir les modèles
        $allBrands = $this->brands->findAll();
        $brandsById = [];
        foreach ($allBrands as $brand) {
            $brandsById[$brand->id()] = $brand->name();
        }

        // Convertir en DTOs avec le nom de la marque
        $modelDtos = array_map(
            function ($model) use ($brandsById) {
                $brandName = $brandsById[$model->brandId()] ?? null;
                return ModelDto::fromModel($model, $brandName);
            },
            $models
        );

        return new ListModelsResponse($modelDtos);
    }
}
