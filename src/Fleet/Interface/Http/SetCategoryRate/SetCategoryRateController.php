<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\SetCategoryRate;

use Fleet\Application\SetCategoryRate\SetCategoryRateCommand;
use Fleet\Application\SetCategoryRate\SetCategoryRateHandler;
use Fleet\Domain\RateDuration;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SetCategoryRateController
{
    public function __construct(
        private readonly SetCategoryRateHandler $handler,
    ) {
    }

    public function __invoke(string $categoryId, SetCategoryRateRequest $request): JsonResponse
    {
        return new JsonResponse([], Response::HTTP_NOT_IMPLEMENTED);
        //$command = new SetCategoryRateCommand(
        //    categoryId: $categoryId,
        //    duration: RateDuration::from($request->input('duration')),
        //    price: (float) $request->input('price'),
        //);
        //
        //$response = $this->handler->handle($command);
        //
        //return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
