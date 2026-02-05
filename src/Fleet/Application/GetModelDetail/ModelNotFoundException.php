<?php

declare(strict_types=1);

namespace Fleet\Application\GetModelDetail;

final class ModelNotFoundException extends \Exception
{
    public function __construct(string $modelId)
    {
        parent::__construct("Model with ID {$modelId} not found");
    }
}
