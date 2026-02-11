<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ListDefaultPricingClasses;

use Illuminate\Http\JsonResponse;
use Pricing\Application\ListDefaultPricingClasses\ListDefaultPricingClassesHandler;
use Pricing\Application\ListDefaultPricingClasses\ListDefaultPricingClassesQuery;

/**
 * Controller pour récupérer les classes tarifaires par défaut (templates).
 */
final class ListDefaultPricingClassesController
{
    public function __construct(
        private readonly ListDefaultPricingClassesHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $query = new ListDefaultPricingClassesQuery();
        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
