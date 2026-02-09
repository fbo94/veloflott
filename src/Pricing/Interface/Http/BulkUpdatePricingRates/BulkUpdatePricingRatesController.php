<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\BulkUpdatePricingRates;

use Illuminate\Http\JsonResponse;
use Pricing\Application\BulkUpdatePricingRates\BulkUpdatePricingRatesCommand;
use Pricing\Application\BulkUpdatePricingRates\BulkUpdatePricingRatesHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class BulkUpdatePricingRatesController
{
    public function __construct(
        private BulkUpdatePricingRatesHandler $handler,
    ) {
    }

    public function __invoke(BulkUpdatePricingRatesRequest $request): JsonResponse
    {
        $command = new BulkUpdatePricingRatesCommand(
            rates: $request->validated()['rates'],
        );

        $result = $this->handler->handle($command);

        return new JsonResponse([
            'message' => 'Tarifs mis à jour avec succès',
            'created' => $result['created'],
            'updated' => $result['updated'],
            'total' => $result['created'] + $result['updated'],
        ], Response::HTTP_OK);
    }
}
