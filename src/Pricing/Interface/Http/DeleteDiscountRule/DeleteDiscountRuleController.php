<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\DeleteDiscountRule;

use Illuminate\Http\JsonResponse;
use Pricing\Application\DeleteDiscountRule\DeleteDiscountRuleCommand;
use Pricing\Application\DeleteDiscountRule\DeleteDiscountRuleHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class DeleteDiscountRuleController
{
    public function __construct(
        private DeleteDiscountRuleHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteDiscountRuleCommand(id: $id);

        try {
            $this->handler->handle($command);

            return new JsonResponse([
                'message' => 'Règle de réduction supprimée avec succès',
            ], Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
