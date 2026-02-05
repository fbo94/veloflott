<?php

declare(strict_types=1);

namespace Maintenance\Domain;

enum MaintenanceCategory: string
{
    case TRANSMISSION = 'transmission';
    case BRAKES = 'brakes';
    case SUSPENSION = 'suspension';
    case WHEELS = 'wheels';
    case STEERING = 'steering';
    case FRAME = 'frame';
    case ELECTRICAL = 'electrical';
    case FULL_SERVICE = 'full_service';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TRANSMISSION => 'Transmission',
            self::BRAKES => 'Freinage',
            self::SUSPENSION => 'Suspensions',
            self::WHEELS => 'Roues',
            self::STEERING => 'Direction',
            self::FRAME => 'Cadre',
            self::ELECTRICAL => 'Électrique (VAE)',
            self::FULL_SERVICE => 'Révision complète',
            self::OTHER => 'Autre',
        };
    }
}
