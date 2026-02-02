<?php

declare(strict_types=1);

namespace Fleet\Domain;

interface BikeRepositoryInterface
{
    public function findById(string $id): ?Bike;

    public function findByQrCodeUuid(string $qrCodeUuid): ?Bike;

    public function findByInternalNumber(string $internalNumber): ?Bike;

    /**
     * @return Bike[]
     */
    public function findAll(
        ?BikeStatus $status = null,
        ?string $categoryId = null,
        ?FrameSizeLetter $frameSize = null,
        bool $includeRetired = false,
    ): array;

    /**
     * @return Bike[]
     */
    public function search(string $query): array;

    /**
     * @param string[]|null $statuses
     * @param string[]|null $categoryIds
     * @param string[]|null $frameSizes
     * @return array{bikes: Bike[], total: int}
     */
    public function findFiltered(
        ?array $statuses = null,
        ?array $categoryIds = null,
        ?array $frameSizes = null,
        bool $includeRetired = false,
        ?string $search = null,
        string $sortBy = 'internal_number',
        string $sortDirection = 'asc',
        int $page = 1,
        int $perPage = 50,
    ): array;

    public function countByCategoryId(string $categoryId): int;

    /**
     * Trouve un vélo par ID avec les relations chargées (pour les DTOs).
     * Retourne le model Eloquent pour accès direct aux relations.
     */
    public function findByIdWithRelations(string $id): ?\Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

    public function save(Bike $bike): void;
}
