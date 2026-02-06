<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CancelRental;

use Illuminate\Http\JsonResponse;
use Rental\Application\CancelRental\CancelRentalCommand;
use Rental\Application\CancelRental\CancelRentalHandler;
use Rental\Application\CancelRental\CannotCancelRentalException;
use Rental\Application\CancelRental\RentalNotFoundException;

final class CancelRentalController
{
    public function __construct(
        private readonly CancelRentalHandler $handler,
    ) {}

    public function __invoke(string $id, CancelRentalRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $command = new CancelRentalCommand(
                rentalId: $id,
                cancellationReason: $validated['cancellation_reason'],
            );

            $response = $this->handler->handle($command);

            return new JsonResponse($response->toArray(), 200);
        } catch (RentalNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Rental not found'],
                404
            );
        } catch (CannotCancelRentalException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
    }
}
