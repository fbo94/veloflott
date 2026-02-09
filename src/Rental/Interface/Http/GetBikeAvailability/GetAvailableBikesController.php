<?php

declare(strict_types=1);

namespace Rental\Interface\Http\GetBikeAvailability;

use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Symfony\Component\HttpFoundation\Response;

final class GetAvailableBikesController
{
    public function __construct(
        private readonly BikeAvailabilityServiceInterface $availabilityService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate === null || $endDate === null) {
            return new JsonResponse([
                'message' => 'start_date and end_date are required',
            ], Response::HTTP_BAD_REQUEST);
        }

        $startDateTime = new DateTimeImmutable($startDate);
        $endDateTime = new DateTimeImmutable($endDate);

        if ($endDateTime <= $startDateTime) {
            return new JsonResponse([
                'message' => 'end_date must be after start_date',
            ], Response::HTTP_BAD_REQUEST);
        }

        $availableBikes = $this->availabilityService->getAvailableBikesForPeriod(
            $startDateTime,
            $endDateTime,
            $request->query('category_id'),
            $request->query('pricing_class_id'),
        );

        return new JsonResponse([
            'start_date' => $startDateTime->format('Y-m-d H:i:s'),
            'end_date' => $endDateTime->format('Y-m-d H:i:s'),
            'available_bikes' => $availableBikes,
            'count' => count($availableBikes),
        ], Response::HTTP_OK);
    }
}
