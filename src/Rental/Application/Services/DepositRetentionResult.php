<?php

declare(strict_types=1);

namespace Rental\Application\Services;

use Rental\Domain\DamageLevel;

final readonly class DepositRetentionResult
{
    public function __construct(
        public float $retentionAmount,
        public float $refundAmount,
        public DamageLevel $damageLevel,
        public ?string $configSource,
    ) {
    }

    public function toArray(): array
    {
        return [
            'retention_amount' => $this->retentionAmount,
            'refund_amount' => $this->refundAmount,
            'damage_level' => $this->damageLevel->value,
            'damage_level_label' => $this->damageLevel->label(),
            'config_source' => $this->configSource,
        ];
    }
}
