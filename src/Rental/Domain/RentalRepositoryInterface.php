<?php

declare(strict_types=1);

namespace Rental\Domain;

interface RentalRepositoryInterface
{
    public function findById(string $id): ?Rental;

    /**
     * @return Rental[]
     */
    public function findActiveRentals(): array;

    /**
     * @return Rental[]
     */
    public function findByCustomerId(string $customerId): array;

    /**
     * @return Rental[]
     */
    public function findLateRentals(): array;

    /**
     * Compte le nombre de locations actives
     */
    public function countActive(): int;

    /**
     * Trouve les locations par période
     * @return Rental[]
     */
    public function findByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): array;

    /**
     * Compte le nombre de locations dans une période
     */
    public function countByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): int;

    /**
     * Calcule le revenu total dans une période (en centimes)
     */
    public function sumRevenueByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): int;

    /**
     * Calcule la durée moyenne de location en heures
     */
    public function getAverageRentalDurationHours(): float;

    /**
     * Trouve les locations qui ont démarré à une date donnée
     * @return Rental[]
     */
    public function findStartedOnDate(\DateTimeImmutable $date): array;

    /**
     * Trouve les locations dont le retour est prévu à une date donnée
     * @return Rental[]
     */
    public function findExpectedReturnOnDate(\DateTimeImmutable $date): array;

    /**
     * Récupère les statistiques par vélo (nombre de locations, revenu total)
     * @return array<int, array{bike_id: string, rental_count: int, total_revenue: int}>
     */
    public function getStatsByBike(?int $limit = null): array;

    /**
     * Trouve les locations d'un vélo spécifique
     * @param string $bikeId
     * @param RentalStatus[]|null $statuses Statuts à filtrer (null = tous)
     * @return Rental[]
     */
    public function findByBikeId(string $bikeId, ?array $statuses = null): array;

    public function save(Rental $rental): void;

    public function saveWithItems(Rental $rental): void;
}
