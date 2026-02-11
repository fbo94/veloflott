<?php

declare(strict_types=1);

namespace Subscription\Interface\Http\ListSubscriptionPlans;

use Illuminate\Http\JsonResponse;
use Subscription\Application\ListSubscriptionPlans\ListSubscriptionPlansHandler;

final readonly class ListSubscriptionPlansController
{
    public function __construct(
        private ListSubscriptionPlansHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $response = $this->handler->handle();

        return response()->json($response->toArray());
    }
}
