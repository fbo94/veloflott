<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ListDefaultDurations;

use Illuminate\Http\JsonResponse;
use Pricing\Application\ListDefaultDurations\ListDefaultDurationsHandler;
use Pricing\Application\ListDefaultDurations\ListDefaultDurationsQuery;

/**
 * Controller pour récupérer les durées par défaut (templates).
 */
final class ListDefaultDurationsController
{
    public function __construct(
        private readonly ListDefaultDurationsHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $query = new ListDefaultDurationsQuery();
        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
