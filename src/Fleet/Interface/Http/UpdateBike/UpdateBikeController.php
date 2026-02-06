<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateBike;

use Fleet\Application\UpdateBike\BikeNotFoundException;
use Fleet\Application\UpdateBike\UpdateBikeCommand;
use Fleet\Application\UpdateBike\UpdateBikeHandler;
use Illuminate\Http\JsonResponse;

final readonly class UpdateBikeController
{
    public function __construct(
        private UpdateBikeHandler $handler,
    ) {}

    public function __invoke(string $id, UpdateBikeRequest $request): JsonResponse
    {
        try {
            $command = new UpdateBikeCommand(
                bikeId: $id,
                modelId: $request->validated('model_id'),
                categoryId: $request->validated('category_id'),
                frameSizeUnit: $request->validated('frame_size_unit'),
                frameSizeNumeric: $request->validated('frame_size_numeric'),
                frameSizeLetter: $request->validated('frame_size_letter'),
                year: $request->validated('year'),
                serialNumber: $request->validated('serial_number'),
                color: $request->validated('color'),
                wheelSize: $request->validated('wheel_size'),
                frontSuspension: $request->validated('front_suspension'),
                rearSuspension: $request->validated('rear_suspension'),
                brakeType: $request->validated('brake_type'),
                purchasePrice: $request->validated('purchase_price'),
                purchaseDate: $request->validated('purchase_date'),
                notes: $request->validated('notes'),
                photos: $request->validated('photos'),
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
