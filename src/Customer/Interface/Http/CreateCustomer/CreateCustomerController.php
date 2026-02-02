<?php

declare(strict_types=1);

namespace Customer\Interface\Http\CreateCustomer;

use Customer\Application\CreateCustomer\CreateCustomerCommand;
use Customer\Application\CreateCustomer\CreateCustomerHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateCustomerController
{
    public function __construct(
        private readonly CreateCustomerHandler $handler,
    ) {}

    public function __invoke(CreateCustomerRequest $request): JsonResponse
    {
        $command = new CreateCustomerCommand(
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            notes: $request->input('notes'),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
