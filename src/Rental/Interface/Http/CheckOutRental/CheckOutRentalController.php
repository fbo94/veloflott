<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CheckOutRental;

use Rental\Application\CheckOutRental\BikeConditionData;
use Rental\Application\CheckOutRental\CheckOutRentalCommand;
use Rental\Application\CheckOutRental\CheckOutRentalHandler;
use Rental\Domain\BikeCondition;
use Illuminate\Http\JsonResponse;

final class CheckOutRentalController
{
    public function __construct(
        private readonly CheckOutRentalHandler $handler,
    ) {
    }

    public function __invoke(string $id, CheckOutRentalRequest $request): JsonResponse
    {
        $bikesCondition = array_map(
            fn ($bike) => new BikeConditionData(
                bikeId: $bike['bike_id'],
                condition: BikeCondition::from($bike['condition']),
                damageDescription: $bike['damage_description'] ?? null,
                damagePhotos: $bike['damage_photos'] ?? null,
            ),
            $request->input('bikes_condition')
        );

        $command = new CheckOutRentalCommand(
            rentalId: $id,
            actualReturnDate: new \DateTimeImmutable($request->input('actual_return_date')),
            bikesCondition: $bikesCondition,
            depositRetained: (float) $request->input('deposit_retained', 0.0),
            hourlyLateRate: (float) $request->input('hourly_late_rate', 10.0),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray());
    }
}
