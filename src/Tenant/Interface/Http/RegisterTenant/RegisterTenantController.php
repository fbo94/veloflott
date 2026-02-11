<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\RegisterTenant;

use Illuminate\Http\JsonResponse;
use Tenant\Application\RegisterTenant\RegisterTenantCommand;
use Tenant\Application\RegisterTenant\RegisterTenantHandler;

final readonly class RegisterTenantController
{
    public function __construct(
        private RegisterTenantHandler $handler,
    ) {
    }

    public function __invoke(RegisterTenantRequest $request): JsonResponse
    {
        $command = new RegisterTenantCommand(
            ownerName: $request->input('owner_name'),
            ownerEmail: $request->input('owner_email'),
            organizationName: $request->input('organization_name'),
            subscriptionPlanId: $request->input('subscription_plan_id'),
        );

        try {
            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 201);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => 'Registration error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
