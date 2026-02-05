<?php

declare(strict_types=1);

namespace Rental\Domain;

enum DepositStatus: string
{
    case HELD = 'held';             // Caution bloquée
    case RELEASED = 'released';     // Caution libérée totalement
    case PARTIAL = 'partial';       // Caution partiellement retenue
    case RETAINED = 'retained';     // Caution totalement retenue
}
