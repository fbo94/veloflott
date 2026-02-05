<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CheckInRental;

use Illuminate\Http\JsonResponse;
use Rental\Application\CheckInRental\BikeCheckInData;
use Rental\Application\CheckInRental\CheckInRentalCommand;
use Rental\Application\CheckInRental\CheckInRentalHandler;

final readonly class CheckInRentalController
{
    public function __construct(
        private CheckInRentalHandler $handler,
    ) {
    }

    public function __invoke(string $id, CheckInRentalRequest $request): JsonResponse
    {
        $bikesCheckIn = array_map(
            fn (array $bike) => new BikeCheckInData(
                bikeId: $bike['bike_id'],
                clientHeight: $bike['client_height'],
                clientWeight: $bike['client_weight'],
                saddleHeight: $bike['saddle_height'],
                frontSuspensionPressure: $bike['front_suspension_pressure'] ?? null,
                rearSuspensionPressure: $bike['rear_suspension_pressure'] ?? null,
                pedalType: $bike['pedal_type'] ?? null,
                notes: $bike['notes'] ?? null,
            ),
            $request->validated()['bikes_check_in']
        );

        $command = new CheckInRentalCommand(
            rentalId: $id,
            bikesCheckIn: $bikesCheckIn,
            customerSignature: $request->validated()['customer_signature'] ?? null,
        );

        $response = $this->handler->handle($command);

        return response()->json([
            'rental_id' => $response->rentalId,
            'message' => $response->message,
        ], 200);
    }
}
