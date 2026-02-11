<?php

declare(strict_types=1);

namespace Rental\Interface\Http\ChangeRentalStatus;

use Illuminate\Http\JsonResponse;
use Rental\Application\ChangeRentalStatus\ChangeRentalStatusCommand;
use Rental\Application\ChangeRentalStatus\ChangeRentalStatusHandler;
use Rental\Domain\Exceptions\RentalException;
use Rental\Domain\RentalStatus;
use Symfony\Component\HttpFoundation\Response;

final class ChangeRentalStatusController
{
    public function __construct(
        private readonly ChangeRentalStatusHandler $handler,
    ) {
    }

    public function __invoke(ChangeRentalStatusRequest $request, string $id): JsonResponse
    {
        try {
            $command = new ChangeRentalStatusCommand(
                rentalId: $id,
                newStatus: RentalStatus::from($request->validated('status')),
                reason: $request->validated('reason'),
            );

            $response = $this->handler->handle($command);

            return new JsonResponse([
                'rental_id' => $response->rentalId,
                'previous_status' => $response->previousStatus,
                'new_status' => $response->newStatus,
                'message' => $response->message,
            ], Response::HTTP_OK);
        } catch (RentalException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: Response::HTTP_BAD_REQUEST);
        }
    }
}
