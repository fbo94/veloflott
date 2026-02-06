<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\GetMaintenanceDetail;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\GetMaintenanceDetail\GetMaintenanceDetailHandler;
use Maintenance\Application\GetMaintenanceDetail\GetMaintenanceDetailQuery;
use Maintenance\Application\GetMaintenanceDetail\MaintenanceNotFoundException;

/**
 * @OA\Get(
 *     path="/api/maintenances/{id}",
 *     summary="Get maintenance details",
 *     description="Retrieve detailed information about a specific maintenance including bike data",
 *     operationId="getMaintenanceDetail",
 *     tags={"Maintenances"},
 *     security={{"bearerAuth": {}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Maintenance ID",
 *         required=true,
 *
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Maintenance details retrieved successfully",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *             @OA\Property(property="bike_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174001"),
 *             @OA\Property(property="type", type="string", enum={"preventive", "corrective", "inspection"}, example="preventive"),
 *             @OA\Property(property="reason", type="string", enum={"scheduled", "breakdown", "incident", "user_report"}, example="scheduled"),
 *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, example="medium"),
 *             @OA\Property(property="status", type="string", enum={"todo", "in_progress", "completed"}, example="in_progress"),
 *             @OA\Property(property="description", type="string", nullable=true, example="Routine maintenance check"),
 *             @OA\Property(property="scheduled_at", type="string", format="date-time", example="2026-02-05 10:00:00"),
 *             @OA\Property(property="started_at", type="string", format="date-time", nullable=true, example="2026-02-05 10:15:00"),
 *             @OA\Property(property="completed_at", type="string", format="date-time", nullable=true, example=null),
 *             @OA\Property(property="work_description", type="string", nullable=true, example=null),
 *             @OA\Property(property="parts_replaced", type="string", nullable=true, example=null),
 *             @OA\Property(property="cost", type="number", format="float", nullable=true, example=null, description="Cost in euros"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2026-02-01 14:30:00"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2026-02-05 10:15:00"),
 *             @OA\Property(
 *                 property="bike",
 *                 type="object",
 *                 nullable=true,
 *                 @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174001"),
 *                 @OA\Property(property="internal_number", type="string", example="BIKE-001"),
 *                 @OA\Property(property="brand", type="string", example="Trek"),
 *                 @OA\Property(property="model", type="string", example="FX 3"),
 *                 @OA\Property(property="category_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174002"),
 *                 @OA\Property(property="category_name", type="string", example="Hybrid Bikes"),
 *                 @OA\Property(property="status", type="string", example="in_maintenance"),
 *                 @OA\Property(property="purchase_price", type="number", format="float", nullable=true, example=899.99),
 *                 @OA\Property(property="purchase_date", type="string", format="date", nullable=true, example="2025-01-15"),
 *                 @OA\Property(property="frame_size_cm", type="integer", nullable=true, example=54),
 *                 @OA\Property(property="frame_size_inches", type="number", format="float", nullable=true, example=21.0),
 *                 @OA\Property(property="frame_size_letter_equivalent", type="string", nullable=true, example="m"),
 *                 @OA\Property(property="color", type="string", nullable=true, example="Black"),
 *                 @OA\Property(property="serial_number", type="string", nullable=true, example="TRK123456789")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Maintenance not found",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(property="message", type="string", example="Maintenance with ID 123e4567-e89b-12d3-a456-426614174000 not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Invalid or missing authentication token"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Insufficient permissions"
 *     )
 * )
 */
final readonly class GetMaintenanceDetailController
{
    public function __construct(
        private GetMaintenanceDetailHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        try {
            $query = new GetMaintenanceDetailQuery(maintenanceId: $id);
            $response = $this->handler->handle($query);

            return response()->json($response->toArray());
        } catch (MaintenanceNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
