<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\RetireBike;

use Fleet\Application\RetireBike\RetireBikeCommand;
use Fleet\Application\RetireBike\RetireBikeHandler;
use Fleet\Application\UpdateBike\BikeNotFoundException;
use Illuminate\Http\JsonResponse;

final readonly class RetireBikeController
{
    public function __construct(
        private RetireBikeHandler $handler,
    ) {}

    public function __invoke(string $id, RetireBikeRequest $request): JsonResponse
    {
        try {
            $command = new RetireBikeCommand(
                bikeId: $id,
                reason: $request->validated('reason'),
                comment: $request->validated('comment'),
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
