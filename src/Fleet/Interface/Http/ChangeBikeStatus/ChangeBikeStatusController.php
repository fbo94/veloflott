<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ChangeBikeStatus;

use Fleet\Application\ChangeBikeStatus\ChangeBikeStatusCommand;
use Fleet\Application\ChangeBikeStatus\ChangeBikeStatusHandler;
use Fleet\Application\UpdateBike\BikeNotFoundException;
use Illuminate\Http\JsonResponse;

final readonly class ChangeBikeStatusController
{
    public function __construct(
        private ChangeBikeStatusHandler $handler,
    ) {}

    public function __invoke(string $id, ChangeBikeStatusRequest $request): JsonResponse
    {
        try {
            $command = new ChangeBikeStatusCommand(
                bikeId: $id,
                status: $request->validated('status'),
                unavailabilityReason: $request->validated('unavailability_reason'),
                unavailabilityComment: $request->validated('unavailability_comment'),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray());
        } catch (BikeNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                400
            );
        }
    }
}
