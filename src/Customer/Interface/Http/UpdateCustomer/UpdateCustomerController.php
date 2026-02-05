<?php

declare(strict_types=1);

namespace Customer\Interface\Http\UpdateCustomer;

use Customer\Application\UpdateCustomer\UpdateCustomerCommand;
use Customer\Application\UpdateCustomer\UpdateCustomerHandler;
use Illuminate\Http\JsonResponse;

final readonly class UpdateCustomerController
{
    public function __construct(
        private UpdateCustomerHandler $handler,
    ) {}

    public function __invoke(string $id, UpdateCustomerRequest $request): JsonResponse
    {
        $command = new UpdateCustomerCommand(
            customerId: $id,
            firstName: $request->input('first_name'),
            lastName: $request->input('last_name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            identityDocumentType: $request->input('identity_document_type'),
            identityDocumentNumber: $request->input('identity_document_number'),
            height: $request->input('height'),
            weight: $request->input('weight'),
            address: $request->input('address'),
            notes: $request->input('notes'),
        );

        $response = $this->handler->handle($command);

        return response()->json($response->toArray(), 200);
    }
}
