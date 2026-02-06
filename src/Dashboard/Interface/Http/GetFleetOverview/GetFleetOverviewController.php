<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetFleetOverview;

use Dashboard\Application\GetFleetOverview\GetFleetOverviewHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetFleetOverviewController
{
    public function __construct(
        private readonly GetFleetOverviewHandler $handler,
    ) {}

    public function __invoke(): JsonResponse
    {
        $response = $this->handler->handle();

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
