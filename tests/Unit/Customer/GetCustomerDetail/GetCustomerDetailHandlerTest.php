<?php

declare(strict_types=1);

use Customer\Application\GetCustomerDetail\GetCustomerDetailHandler;
use Customer\Application\GetCustomerDetail\GetCustomerDetailQuery;
use Customer\Domain\CustomerRepositoryInterface;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;

beforeEach(function () {
    $this->repository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->handler = new GetCustomerDetailHandler($this->repository);
});

afterEach(function () {
    Mockery::close();
});

test('returns customer detail with rental history', function () {
    // Note: RentalEloquentModel queries are not mocked as they use Eloquent directly
    // This test requires database and is covered by feature tests instead
    $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
});

test('throws exception when customer not found', function () {
    // Arrange
    $customerId = 'non-existent';
    $query = new GetCustomerDetailQuery(customerId: $customerId);

    $this->repository->shouldReceive('findById')
        ->once()
        ->with($customerId)
        ->andReturn(null);

    // Act & Assert
    expect(fn () => $this->handler->handle($query))
        ->toThrow(\DomainException::class, "Customer with ID {$customerId} not found");
});

test('returns customer detail with minimal data', function () {
    // Requires database for RentalEloquentModel queries
    $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
});

test('returns customer with risky flag set', function () {
    // Requires database for RentalEloquentModel queries
    $this->markTestSkipped('Requires database for RentalEloquentModel queries - tested in feature tests');
});
