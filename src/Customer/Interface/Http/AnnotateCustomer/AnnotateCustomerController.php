<?php

declare(strict_types=1);

namespace Customer\Interface\Http\AnnotateCustomer;

use Customer\Application\AnnotateCustomer\AnnotateCustomerCommand;
use Customer\Application\AnnotateCustomer\AnnotateCustomerHandler;
use Illuminate\Http\JsonResponse;

final readonly class AnnotateCustomerController
{
    public function __construct(
        private AnnotateCustomerHandler $handler,
    ) {
    }

    public function __invoke(string $id, AnnotateCustomerRequest $request): JsonResponse
    {
        try {
            $command = new AnnotateCustomerCommand(
                customerId: $id,
                annotation: $request->input('annotation'),
                isRiskyCustomer: $request->boolean('is_risky_customer'),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 200);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
