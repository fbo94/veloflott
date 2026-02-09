<?php

declare(strict_types=1);

namespace Customer\Interface\Http\GetCustomerDetail;

use Customer\Application\GetCustomerDetail\GetCustomerDetailHandler;
use Customer\Application\GetCustomerDetail\GetCustomerDetailQuery;
use Illuminate\Http\JsonResponse;

final readonly class GetCustomerDetailController
{
    public function __construct(
        private GetCustomerDetailHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $query = new GetCustomerDetailQuery(customerId: $id);
        $customerDetail = $this->handler->handle($query);

        return response()->json($customerDetail->toArray(), 200);
    }
}
