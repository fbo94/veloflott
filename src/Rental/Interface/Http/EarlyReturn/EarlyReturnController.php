<?php

declare(strict_types=1);

namespace Rental\Interface\Http\EarlyReturn;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Rental\Application\CheckOutRental\BikeConditionData;
use Rental\Application\EarlyReturn\CannotEarlyReturnException;
use Rental\Application\EarlyReturn\EarlyReturnCommand;
use Rental\Application\EarlyReturn\EarlyReturnHandler;
use Rental\Application\EarlyReturn\NotAnEarlyReturnException;
use Symfony\Component\HttpFoundation\Response;

final class EarlyReturnController
{
    public function __construct(
        private readonly EarlyReturnHandler $handler,
    ) {
    }

    public function __invoke(EarlyReturnRequest $request, string $id): JsonResponse
    {
        $bikesCondition = array_map(
            fn ($condition) => new BikeConditionData(
                bikeId: $condition['bike_id'],
                condition: $condition['condition'],
                damageDescription: $condition['damage_description'] ?? null,
                damagePhotos: $condition['damage_photos'] ?? null,
            ),
            $request->input('bikes_condition'),
        );

        try {
            $command = new EarlyReturnCommand(
                rentalId: $id,
                actualReturnDate: new DateTimeImmutable($request->input('actual_return_date')),
                bikesCondition: $bikesCondition,
                depositRetained: $request->input('deposit_retained') !== null
                    ? (float) $request->input('deposit_retained')
                    : null,
            );

            $response = $this->handler->handle($command);

            return new JsonResponse($response->toArray(), Response::HTTP_OK);
        } catch (CannotEarlyReturnException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'rental_id' => $e->rentalId,
                'current_status' => $e->currentStatus->value,
            ], Response::HTTP_BAD_REQUEST);
        } catch (NotAnEarlyReturnException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'rental_id' => $e->rentalId,
                'actual_return_date' => $e->actualReturnDate->format('Y-m-d H:i:s'),
                'expected_return_date' => $e->expectedReturnDate->format('Y-m-d H:i:s'),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
