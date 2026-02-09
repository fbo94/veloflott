<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateBike;

use Fleet\Application\CreateBike\BikeInternalNumberAlreadyExistsException;
use Fleet\Application\CreateBike\CategoryNotFoundException;
use Fleet\Application\CreateBike\CreateBikeCommand;
use Fleet\Application\CreateBike\CreateBikeHandler;
use Fleet\Application\CreateBike\ModelNotFoundException;
use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;
use Fleet\Domain\WheelSize;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateBikeController
{
    public function __construct(
        private readonly CreateBikeHandler $handler,
    ) {
    }

    /**
     * @throws BikeInternalNumberAlreadyExistsException
     * @throws CategoryNotFoundException
     * @throws ModelNotFoundException
     */
    public function __invoke(CreateBikeRequest $request): JsonResponse
    {
        $command = new CreateBikeCommand(
            internalNumber: $request->input('internal_number'),
            modelId: $request->input('model_id'),
            categoryId: $request->input('category_id'),
            frameSizeUnit: FrameSizeUnit::from($request->input('frame_size_unit')),
            frameSizeNumeric: $request->input('frame_size_numeric') !== null
                ? (float) $request->input('frame_size_numeric')
                : null,
            frameSizeLetter: $request->input('frame_size_letter') !== null
                ? FrameSizeLetter::from($request->input('frame_size_letter'))
                : null,
            year: $request->input('year') !== null ? (int) $request->input('year') : null,
            serialNumber: $request->input('serial_number'),
            color: $request->input('color'),
            wheelSize: $request->input('wheel_size') !== null
                ? WheelSize::from($request->input('wheel_size'))
                : null,
            frontSuspension: $request->input('front_suspension') !== null
                ? (int) $request->input('front_suspension')
                : null,
            rearSuspension: $request->input('rear_suspension') !== null
                ? (int) $request->input('rear_suspension')
                : null,
            brakeType: $request->input('brake_type') !== null
                ? BrakeType::from($request->input('brake_type'))
                : null,
            purchasePrice: $request->input('purchase_price') !== null
                ? (float) $request->input('purchase_price')
                : null,
            purchaseDate: $request->input('purchase_date') !== null
                ? new \DateTimeImmutable($request->input('purchase_date'))
                : null,
            notes: $request->input('notes'),
            photos: $request->input('photos', []),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
