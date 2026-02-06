<?php

declare(strict_types=1);

namespace Fleet\Application\CreateModel;

use Fleet\Domain\Model;
use Fleet\Domain\ModelRepositoryInterface;
use Illuminate\Support\Str;

final readonly class CreateModelHandler
{
    public function __construct(
        private ModelRepositoryInterface $modelRepository,
    ) {}

    public function handle(CreateModelCommand $command): CreateModelResponse
    {
        $model = new Model(
            id: Str::uuid()->toString(),
            name: $command->name,
            brandId: $command->brandId,
            createdAt: new \DateTimeImmutable,
            updatedAt: new \DateTimeImmutable,
        );

        $this->modelRepository->save($model);

        return new CreateModelResponse(
            id: $model->id(),
            name: $model->name(),
            brandId: $model->brandId(),
        );
    }
}
