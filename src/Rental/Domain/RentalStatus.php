<?php

declare(strict_types=1);

namespace Rental\Domain;

enum RentalStatus: string
{
    case RESERVED = 'reserved';     // Réservation future, dates bloquées, vélo reste AVAILABLE
    case PENDING = 'pending';       // Client présent, prêt pour check-in
    case ACTIVE = 'active';         // Location en cours, vélo RENTED
    case COMPLETED = 'completed';   // Location terminée et check-out effectué
    case CANCELLED = 'cancelled';   // Location annulée

    public function label(): string
    {
        return match ($this) {
            self::RESERVED => 'Réservé',
            self::PENDING => 'En attente',
            self::ACTIVE => 'En cours',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
        };
    }

    /**
     * Vérifie si la location peut passer en PENDING (client arrive)
     */
    public function canConfirm(): bool
    {
        return $this === self::RESERVED;
    }

    /**
     * Vérifie si le check-in est possible (passage en ACTIVE)
     */
    public function canStart(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Vérifie si le check-out est possible (restitution)
     */
    public function canCheckOut(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Vérifie si l'annulation est possible
     * Seules les locations RESERVED et PENDING peuvent être annulées
     * ACTIVE ne peut PAS être annulée (utiliser restitution anticipée)
     */
    public function canCancel(): bool
    {
        return $this === self::RESERVED || $this === self::PENDING;
    }

    /**
     * Vérifie si la restitution anticipée est possible
     */
    public function canEarlyReturn(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Vérifie si les dates sont bloquées dans le calendrier
     */
    public function blocksCalendarDates(): bool
    {
        return in_array($this, [self::RESERVED, self::PENDING, self::ACTIVE], true);
    }

    /**
     * Vérifie si le vélo est physiquement bloqué (statut RENTED)
     */
    public function blocksBikePhysically(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Vérifie si la location est dans un état final
     */
    public function isFinal(): bool
    {
        return $this === self::COMPLETED || $this === self::CANCELLED;
    }
}
