<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\UpdateTenant;

use Illuminate\Http\JsonResponse;
use Tenant\Application\UpdateTenant\UpdateTenantCommand;
use Tenant\Application\UpdateTenant\UpdateTenantHandler;

final readonly class UpdateTenantController
{
    public function __construct(
        private UpdateTenantHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdateTenantRequest $request): JsonResponse
    {
        $command = new UpdateTenantCommand(
            id: $id,
            name: $request->input('name'),
            slug: $request->input('slug'),
            contactEmail: $request->input('contact_email'),
            contactPhone: $request->input('contact_phone'),
            address: $request->input('address'),
            logoUrl: $request->input('logo_url'),
            subscriptionPlanId: $request->input('subscription_plan_id'),
            maxUsers: (int) $request->input('max_users'),
            maxBikes: (int) $request->input('max_bikes'),
            maxSites: (int) $request->input('max_sites'),
        );

        $response = $this->handler->handle($command);

        return response()->json($response->toArray());
    }
}
