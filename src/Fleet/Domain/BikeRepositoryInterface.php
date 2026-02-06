<?php

declare(strict_types=1);

namespace Fleet\Domain;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

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
     * @param  string[]|null  $statuses
     * @param  string[]|null  $categoryIds
     * @param  string[]|null  $frameSizes
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
     * Compte le nombre de vélos par statut
     *
     * @return array<string, int> ex: ['available' => 95, 'rented' => 40, ...]
     */
    public function countByStatus(): array;

    /**
     * Compte le nombre total de vélos actifs (hors RETIRED)
     */
    public function countActive(): int;

    /**
     * Calcule l'âge moyen de la flotte en années
     */
    public function getAverageAge(): float;

    /**
     * Trouve les vélos UNAVAILABLE depuis plus de X jours
     *
     * @return array<int, array{bike_id: string, internal_number: string, days_unavailable: int}>
     */
    public function findLongUnavailable(int $minDays = 5): array;

    /**
     * Trouve un vélo par ID avec les relations chargées (pour les DTOs).
     * Retourne le model Eloquent pour accès direct aux relations.
     */
    public function findByIdWithRelations(string $id): ?BikeEloquentModel;

    public function save(Bike $bike): void;
}
