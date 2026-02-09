<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\DeleteDuration;

use Illuminate\Http\JsonResponse;
use Pricing\Application\DeleteDuration\DeleteDurationCommand;
use Pricing\Application\DeleteDuration\DeleteDurationHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class DeleteDurationController
{
    public function __construct(
        private DeleteDurationHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteDurationCommand(id: $id);

        try {
            $this->handler->handle($command);

            return new JsonResponse([
                'message' => 'Durée supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
