<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateModel;

use Fleet\Domain\ModelRepositoryInterface;

final readonly class UpdateModelHandler
{
    public function __construct(
        private ModelRepositoryInterface $modelRepository,
    ) {}

    public function handle(UpdateModelCommand $command): UpdateModelResponse
    {
        $model = $this->modelRepository->findById($command->id);

        if ($model === null) {
            throw new ModelNotFoundException($command->id);
        }

        $model->rename($command->name);
        $this->modelRepository->save($model);

        return new UpdateModelResponse(
            id: $model->id(),
            name: $model->name(),
            brandId: $model->brandId(),
        );
    }
}
