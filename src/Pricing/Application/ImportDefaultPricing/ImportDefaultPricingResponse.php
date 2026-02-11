<?php

declare(strict_types=1);

namespace Pricing\Application\ImportDefaultPricing;

/**
 * Response de l'import de la grille tarifaire par défaut.
 */
final readonly class ImportDefaultPricingResponse
{
    /**
     * @param array{pricing_classes: int, durations: int, rates: int, discount_rules: int} $imported
     */
    public function __construct(
        public bool $success,
        public string $message,
        public array $imported,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'imported' => $this->imported,
        ];
    }
}
