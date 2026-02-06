<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetCentralizedAlerts;

use Dashboard\Application\GetCentralizedAlerts\GetCentralizedAlertsHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetCentralizedAlertsController
{
    public function __construct(
        private readonly GetCentralizedAlertsHandler $handler,
    ) {}

    public function __invoke(): JsonResponse
    {
        $response = $this->handler->handle();

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
