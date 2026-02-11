<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\CreateTenant;

use Illuminate\Http\JsonResponse;
use Tenant\Application\CreateTenant\CreateTenantCommand;
use Tenant\Application\CreateTenant\CreateTenantHandler;

final readonly class CreateTenantController
{
    public function __construct(
        private CreateTenantHandler $handler,
    ) {
    }

    public function __invoke(CreateTenantRequest $request): JsonResponse
    {
        $command = new CreateTenantCommand(
            name: $request->input('name'),
            slug: $request->input('slug'),
            contactEmail: $request->input('contact_email'),
            contactPhone: $request->input('contact_phone'),
            address: $request->input('address'),
            logoUrl: $request->input('logo_url'),
            subscriptionPlanId: $request->input('subscription_plan_id'),
        );

        try {
            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 201);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => 'Validation error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
