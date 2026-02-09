<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteModel;

use Fleet\Domain\ModelRepositoryInterface;

final readonly class DeleteModelHandler
{
    public function __construct(
        private ModelRepositoryInterface $modelRepository,
    ) {
    }

    public function handle(DeleteModelCommand $command): void
    {
        $this->modelRepository->delete($command->id);
    }
}
