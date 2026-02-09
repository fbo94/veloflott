<?php

declare(strict_types=1);

namespace Rental\Interface\Http\GetBikeAvailability;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Symfony\Component\HttpFoundation\Response;

final class GetBikeAvailabilityController
{
    public function __construct(
        private readonly BikeAvailabilityServiceInterface $availabilityService,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $from = $request->query('from')
            ? new DateTimeImmutable($request->query('from'))
            : new DateTimeImmutable('today');

        $to = $request->query('to')
            ? new DateTimeImmutable($request->query('to'))
            : $from->modify('+30 days');

        $slots = $this->availabilityService->getUnavailabilitySlots($id, $from, $to);
        $isPhysicallyAvailable = $this->availabilityService->isPhysicallyAvailable($id);

        return new JsonResponse([
            'bike_id' => $id,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'is_physically_available' => $isPhysicallyAvailable,
            'unavailability_slots' => array_map(fn ($slot) => $slot->toArray(), $slots),
        ], Response::HTTP_OK);
    }
}
