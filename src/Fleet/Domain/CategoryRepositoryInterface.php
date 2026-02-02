<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface CategoryRepositoryInterface
{
    public function findById(string $id): ?Category;

    public function findByName(string $name): ?Category;

    /**
     * @return Category[]
     */
    public function findAll(): array;

    /**
     * @return Category[]
     */
    public function findAllOrdered(): array;

    public function hasBikes(string $categoryId): bool;

    public function save(Category $category): void;

    public function delete(Category $category): void;
}
