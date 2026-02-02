<?php

declare(strict_types=1);

namespace Customer\Domain;

interface CustomerRepositoryInterface
{
    public function findById(string $id): ?Customer;

    public function findByEmail(string $email): ?Customer;

    /**
     * @return Customer[]
     */
    public function search(string $query): array;

    /**
     * @return Customer[]
     */
    public function findAll(): array;

    public function save(Customer $customer): void;
}
