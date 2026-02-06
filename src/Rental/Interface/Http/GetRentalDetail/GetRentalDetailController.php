<?php

declare(strict_types=1);

namespace Rental\Interface\Http\GetRentalDetail;

use Illuminate\Http\JsonResponse;
use Rental\Application\GetRentalDetail\GetRentalDetailHandler;
use Rental\Application\GetRentalDetail\GetRentalDetailQuery;
use Rental\Application\GetRentalDetail\RentalNotFoundException;

final class GetRentalDetailController
{
    public function __construct(
        private readonly GetRentalDetailHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        try {
            $query = new GetRentalDetailQuery($id);
            $response = $this->handler->handle($query);

            return new JsonResponse($response->toArray());
        } catch (RentalNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Rental not found'],
                404
            );
        }
    }
}
