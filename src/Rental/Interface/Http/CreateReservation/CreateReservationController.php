<?php

declare(strict_types=1);

namespace Rental\Interface\Http\CreateReservation;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Rental\Application\CreateRental\BikeItemData;
use Rental\Application\CreateRental\EquipmentItemData;
use Rental\Application\CreateReservation\BikeNotAvailableForPeriodException;
use Rental\Application\CreateReservation\CreateReservationCommand;
use Rental\Application\CreateReservation\CreateReservationHandler;
use Rental\Domain\EquipmentType;
use Rental\Domain\RentalDuration;
use Symfony\Component\HttpFoundation\Response;

final class CreateReservationController
{
    public function __construct(
        private readonly CreateReservationHandler $handler,
    ) {
    }

    public function __invoke(CreateReservationRequest $request): JsonResponse
    {
        $bikeItems = array_map(
            fn ($bike) => new BikeItemData(
                bikeId: $bike['bike_id'],
                dailyRate: (float) $bike['daily_rate'],
                quantity: $bike['quantity'] ?? 1,
            ),
            $request->input('bikes'),
        );

        $equipmentItems = array_map(
            fn ($equipment) => new EquipmentItemData(
                type: EquipmentType::from($equipment['type']),
                quantity: (int) $equipment['quantity'],
                pricePerUnit: (float) $equipment['price_per_unit'],
            ),
            $request->input('equipments', []),
        );

        try {
            $command = new CreateReservationCommand(
                customerId: $request->input('customer_id'),
                startDate: new DateTimeImmutable($request->input('start_date')),
                duration: RentalDuration::from($request->input('duration')),
                customEndDate: $request->input('custom_end_date') !== null
                    ? new DateTimeImmutable($request->input('custom_end_date'))
                    : null,
                depositAmount: (float) $request->input('deposit_amount'),
                bikeItems: $bikeItems,
                equipmentItems: $equipmentItems,
            );

            $response = $this->handler->handle($command);

            return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
        } catch (BikeNotAvailableForPeriodException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'bike_id' => $e->bikeId,
                'start_date' => $e->startDate->format('Y-m-d H:i:s'),
                'end_date' => $e->endDate->format('Y-m-d H:i:s'),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
