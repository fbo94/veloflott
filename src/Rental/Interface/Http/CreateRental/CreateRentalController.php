<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CreateRental;

use Illuminate\Http\JsonResponse;
use Rental\Application\CreateRental\BikeItemData;
use Rental\Application\CreateRental\CreateRentalCommand;
use Rental\Application\CreateRental\CreateRentalHandler;
use Rental\Application\CreateRental\EquipmentItemData;
use Rental\Domain\EquipmentType;
use Rental\Domain\RentalDuration;
use Symfony\Component\HttpFoundation\Response;

final class CreateRentalController
{
    public function __construct(
        private readonly CreateRentalHandler $handler,
    ) {}

    public function __invoke(CreateRentalRequest $request): JsonResponse
    {
        $bikeItems = array_map(
            fn ($bike) => new BikeItemData(
                bikeId: $bike['bike_id'],
                dailyRate: (float) $bike['daily_rate'],
                quantity: $bike['quantity'] ?? 1,
            ),
            $request->input('bikes')
        );

        $equipmentItems = array_map(
            fn ($equipment) => new EquipmentItemData(
                type: EquipmentType::from($equipment['type']),
                quantity: (int) $equipment['quantity'],
                pricePerUnit: (float) $equipment['price_per_unit'],
            ),
            $request->input('equipments', [])
        );

        $command = new CreateRentalCommand(
            customerId: $request->input('customer_id'),
            startDate: new \DateTimeImmutable($request->input('start_date')),
            duration: RentalDuration::from($request->input('duration')),
            customEndDate: $request->input('custom_end_date') !== null
                ? new \DateTimeImmutable($request->input('custom_end_date'))
                : null,
            depositAmount: (float) $request->input('deposit_amount'),
            bikeItems: $bikeItems,
            equipmentItems: $equipmentItems,
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
