<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\CalculatePrice;

use Illuminate\Http\JsonResponse;
use Pricing\Application\CalculatePrice\CalculatePriceCommand;
use Pricing\Application\CalculatePrice\CalculatePriceHandler;
use Pricing\Domain\Services\NoPricingFoundException;
use Symfony\Component\HttpFoundation\Response;

final class CalculatePriceController
{
    public function __construct(
        private readonly CalculatePriceHandler $handler,
    ) {}

    public function __invoke(CalculatePriceRequest $request): JsonResponse
    {
        try {
            $command = new CalculatePriceCommand(
                categoryId: $request->input('category_id'),
                pricingClassId: $request->input('pricing_class_id'),
                durationId: $request->input('duration_id'),
                customDays: $request->input('custom_days'),
            );

            $result = $this->handler->handle($command);

            return new JsonResponse($result->toArray());
        } catch (NoPricingFoundException $e) {
            return new JsonResponse([
                'error' => 'No pricing found',
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
