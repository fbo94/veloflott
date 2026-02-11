<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ListDefaultDiscountRules;

use Illuminate\Http\JsonResponse;
use Pricing\Application\ListDefaultDiscountRules\ListDefaultDiscountRulesHandler;
use Pricing\Application\ListDefaultDiscountRules\ListDefaultDiscountRulesQuery;

/**
 * Controller pour récupérer les règles de réduction par défaut (templates).
 */
final class ListDefaultDiscountRulesController
{
    public function __construct(
        private readonly ListDefaultDiscountRulesHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $query = new ListDefaultDiscountRulesQuery();
        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
