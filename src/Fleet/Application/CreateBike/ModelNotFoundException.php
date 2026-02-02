<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBike;

use Shared\Domain\DomainException;

final class ModelNotFoundException extends DomainException
{
    protected string $errorCode = 'MODEL_NOT_FOUND';

    public function __construct(private readonly string $modelId)
    {
        parent::__construct("Le modèle '{$modelId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['model_id' => $this->modelId];
    }
}
