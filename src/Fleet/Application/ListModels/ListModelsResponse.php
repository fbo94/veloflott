<?php

declare(strict_types=1);

namespace Fleet\Application\ListModels;

final readonly class ListModelsResponse
{
    /**
     * @param  array<ModelDto>  $models
     */
    public function __construct(
        public array $models,
    ) {
    }

    public function toArray(): array
    {
        return [
            'models' => array_map(
                fn (ModelDto $model) => $model->toArray(),
                $this->models
            ),
        ];
    }
}
