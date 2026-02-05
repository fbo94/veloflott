<?php

declare(strict_types=1);

namespace Maintenance\Domain;

enum MaintenanceStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'À faire',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminé',
        };
    }

    public function canBeStarted(): bool
    {
        return $this === self::TODO;
    }

    public function canBeCompleted(): bool
    {
        return $this === self::TODO || $this === self::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }
}
