<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\SetCategoryRate;

use Fleet\Application\SetCategoryRate\SetCategoryRateCommand;
use Fleet\Application\SetCategoryRate\SetCategoryRateHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SetCategoryRateController
{
    public function __construct(
        private readonly SetCategoryRateHandler $handler,
    ) {}

    public function __invoke(SetCategoryRateRequest $request): JsonResponse
    {
        $command = new SetCategoryRateCommand(
            categoryId: $request->input('category_id'),
            pricingTier: $request->input('pricing_tier'),
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
