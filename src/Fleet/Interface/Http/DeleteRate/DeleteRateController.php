<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\DeleteRate;

use Fleet\Application\DeleteRate\DeleteRateCommand;
use Fleet\Application\DeleteRate\DeleteRateHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class DeleteRateController
{
    public function __construct(
        private readonly DeleteRateHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteRateCommand($id);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
