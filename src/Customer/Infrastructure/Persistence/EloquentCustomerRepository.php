<?php

declare(strict_types=1);

namespace Customer\Infrastructure\Persistence;

use Customer\Domain\Customer;
use Customer\Domain\CustomerRepositoryInterface;
use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;

final class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(string $id): ?Customer
    {
        $model = CustomerEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?Customer
    {
        $model = CustomerEloquentModel::where('email', $email)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Customer[]
     */
    public function search(string $query): array
    {
        return CustomerEloquentModel::where(function ($q) use ($query) {
            $q->where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%");
        })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Customer[]
     */
    public function findAll(): array
    {
        return CustomerEloquentModel::orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function save(Customer $customer): void
    {
        CustomerEloquentModel::updateOrCreate(
            ['id' => $customer->id()],
            [
                'first_name' => $customer->firstName(),
                'last_name' => $customer->lastName(),
                'email' => $customer->email(),
                'phone' => $customer->phone(),
                'notes' => $customer->notes(),
            ]
        );
    }

    private function toDomain(CustomerEloquentModel $model): Customer
    {
        return new Customer(
            id: $model->id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            phone: $model->phone,
            notes: $model->notes,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
