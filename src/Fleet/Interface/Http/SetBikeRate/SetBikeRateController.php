<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\SetBikeRate;

use Fleet\Application\SetBikeRate\SetBikeRateCommand;
use Fleet\Application\SetBikeRate\SetBikeRateHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SetBikeRateController
{
    public function __construct(
        private readonly SetBikeRateHandler $handler,
    ) {}

    public function __invoke(SetBikeRateRequest $request): JsonResponse
    {
        $command = new SetBikeRateCommand(
            bikeId: $request->input('bike_id'),
            dayPrice: (float) $request->input('day_price'),
            halfDayPrice: $request->input('half_day_price') !== null
                ? (float) $request->input('half_day_price')
                : null,
            weekendPrice: $request->input('weekend_price') !== null
                ? (float) $request->input('weekend_price')
                : null,
            weekPrice: $request->input('week_price') !== null
                ? (float) $request->input('week_price')
                : null,
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
