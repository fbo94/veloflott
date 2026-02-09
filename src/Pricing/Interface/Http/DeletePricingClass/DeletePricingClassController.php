<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\DeletePricingClass;

use Illuminate\Http\JsonResponse;
use Pricing\Application\DeletePricingClass\DeletePricingClassCommand;
use Pricing\Application\DeletePricingClass\DeletePricingClassHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class DeletePricingClassController
{
    public function __construct(
        private DeletePricingClassHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeletePricingClassCommand(id: $id);

        try {
            $this->handler->handle($command);

            return new JsonResponse([
                'message' => 'Classe tarifaire supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
