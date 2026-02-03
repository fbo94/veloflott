<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetTodayActivity;

use Dashboard\Application\GetTodayActivity\GetTodayActivityHandler;
use Dashboard\Application\GetTodayActivity\GetTodayActivityQuery;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetTodayActivityController
{
    public function __construct(
        private readonly GetTodayActivityHandler $handler,
    ) {}

    public function __invoke(GetTodayActivityRequest $request): JsonResponse
    {
        $query = new GetTodayActivityQuery(
            date: $request->input('date') !== null
                ? new \DateTimeImmutable($request->input('date'))
                : null,
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
