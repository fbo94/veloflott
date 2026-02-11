<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ListDefaultPricingRates;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pricing\Application\ListDefaultPricingRates\ListDefaultPricingRatesHandler;
use Pricing\Application\ListDefaultPricingRates\ListDefaultPricingRatesQuery;

/**
 * Controller pour récupérer les tarifs par défaut (templates).
 */
final class ListDefaultPricingRatesController
{
    public function __construct(
        private readonly ListDefaultPricingRatesHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = new ListDefaultPricingRatesQuery(
            categoryId: $request->query('category_id'),
            pricingClassId: $request->query('pricing_class_id'),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
