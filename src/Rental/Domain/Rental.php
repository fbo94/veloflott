<?php

declare(strict_types=1);

namespace Rental\Domain;

/**
 * Rental - Agrégat Root pour les locations.
 */
final class Rental
{
    /**
     * @param  RentalItem[]  $items
     * @param  RentalEquipment[]  $equipments
     */
    public function __construct(
        private readonly string $id,
        private readonly string $customerId,
        private readonly \DateTimeImmutable $startDate,
        private readonly \DateTimeImmutable $expectedReturnDate,
        private ?\DateTimeImmutable $actualReturnDate,
        private readonly RentalDuration $duration,
        private float $depositAmount,
        private float $totalAmount,
        private float $discountAmount,
        private float $taxRate,
        private float $taxAmount,
        private float $totalWithTax,
        private RentalStatus $status,
        private array $items,
        private array $equipments,
        private ?DepositStatus $depositStatus,
        private ?float $depositRetained,
        private ?string $cancellationReason,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
        $this->depositStatus = $depositStatus ?? DepositStatus::HELD;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function startDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function expectedReturnDate(): \DateTimeImmutable
    {
        return $this->expectedReturnDate;
    }

    public function actualReturnDate(): ?\DateTimeImmutable
    {
        return $this->actualReturnDate;
    }

    public function duration(): RentalDuration
    {
        return $this->duration;
    }

    public function depositAmount(): float
    {
        return $this->depositAmount;
    }

    public function totalAmount(): float
    {
        return $this->totalAmount;
    }

    public function discountAmount(): float
    {
        return $this->discountAmount;
    }

    public function taxRate(): float
    {
        return $this->taxRate;
    }

    public function taxAmount(): float
    {
        return $this->taxAmount;
    }

    public function totalWithTax(): float
    {
        return $this->totalWithTax;
    }

    public function status(): RentalStatus
    {
        return $this->status;
    }

    /**
     * @return RentalItem[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return RentalEquipment[]
     */
    public function equipments(): array
    {
        return $this->equipments;
    }

    public function depositStatus(): DepositStatus
    {
        return $this->depositStatus;
    }

    public function depositRetained(): ?float
    {
        return $this->depositRetained;
    }

    public function cancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Business Logic =====

    public function isActive(): bool
    {
        return $this->status === RentalStatus::ACTIVE;
    }

    public function isLate(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return new \DateTimeImmutable() > $this->expectedReturnDate;
    }

    public function getDelayInHours(): int
    {
        if (!$this->isLate()) {
            return 0;
        }

        $now = new \DateTimeImmutable();

        return (int) (($now->getTimestamp() - $this->expectedReturnDate->getTimestamp()) / 3600);
    }

    public function calculateLateFeeSupplement(float $hourlyLateRate = 10.0): float
    {
        return $this->getDelayInHours() * $hourlyLateRate;
    }

    public function recalculateTotalAmount(): void
    {
        $days = $this->duration->days();
        if ($this->duration === RentalDuration::CUSTOM) {
            $days = ($this->expectedReturnDate->getTimestamp() - $this->startDate->getTimestamp()) / 86400;
        }

        $bikesAmount = array_reduce(
            $this->items,
            fn ($sum, RentalItem $item) => $sum + $item->calculateAmount($days),
            0.0
        );

        $equipmentsAmount = array_reduce(
            $this->equipments,
            fn ($sum, RentalEquipment $equipment) => $sum + $equipment->calculateAmount(),
            0.0
        );

        $subtotal = $bikesAmount + $equipmentsAmount;
        $this->totalAmount = $subtotal;

        // Recalculer la TVA et le total TTC
        $this->recalculateTax();

        $this->updatedAt = new \DateTimeImmutable();
    }

    public function recalculateTax(): void
    {
        // Les tarifs sont stockés en TTC
        // On extrait la TVA du montant TTC au lieu de l'ajouter
        $subtotalTTC = $this->totalAmount - $this->discountAmount;

        // TVA = montant_TTC × taux / (100 + taux)
        // Ex: 180€ TTC à 20% → TVA = 180 × 20 / 120 = 30€
        $this->taxAmount = round($subtotalTTC * $this->taxRate / (100 + $this->taxRate), 2);

        // Le total TTC reste le même (c'est le sous-total après réduction)
        $this->totalWithTax = $subtotalTTC;
    }

    public function applyDiscount(float $discountAmount): void
    {
        $this->discountAmount = $discountAmount;
        $this->recalculateTax();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ===== Actions =====

    /**
     * Confirm a reservation (RESERVED → PENDING)
     * Called when customer arrives for a future reservation
     */
    public function confirm(): void
    {
        if (!$this->status->canConfirm()) {
            throw new \DomainException('Cannot confirm a rental that is not reserved');
        }

        $this->status = RentalStatus::PENDING;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Start a rental (PENDING → ACTIVE)
     * Called at check-in when bikes are physically handed over
     */
    public function start(): void
    {
        if (!$this->status->canStart()) {
            throw new \DomainException('Cannot start a rental that is not pending');
        }

        $this->status = RentalStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Complete a rental (ACTIVE → COMPLETED)
     * Used for early returns and regular checkouts
     */
    public function complete(
        \DateTimeImmutable $actualReturnDate,
        DepositStatus $depositStatus,
        float $depositRetained = 0.0,
    ): void {
        if (!$this->status->canCheckOut()) {
            throw new \DomainException('Cannot complete a rental that is not active');
        }

        $this->actualReturnDate = $actualReturnDate;
        $this->status = RentalStatus::COMPLETED;
        $this->depositStatus = $depositStatus;
        $this->depositRetained = $depositRetained;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function checkOut(
        \DateTimeImmutable $actualReturnDate,
        float $lateFee = 0.0,
        ?float $depositRetained = null,
    ): void {
        if (!$this->status->canCheckOut()) {
            throw new \DomainException('Cannot check-out a rental that is not active');
        }

        $this->actualReturnDate = $actualReturnDate;
        $this->status = RentalStatus::COMPLETED;

        // Ajouter les frais de retard au total
        if ($lateFee > 0) {
            $this->totalAmount += $lateFee;
        }

        // Gérer la caution
        if ($depositRetained === null || $depositRetained === 0.0) {
            $this->depositStatus = DepositStatus::RELEASED;
            $this->depositRetained = 0.0;
        } elseif ($depositRetained >= $this->depositAmount) {
            $this->depositStatus = DepositStatus::RETAINED;
            $this->depositRetained = $this->depositAmount;
        } else {
            $this->depositStatus = DepositStatus::PARTIAL;
            $this->depositRetained = $depositRetained;
        }

        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Cancel a rental (RESERVED/PENDING → CANCELLED)
     * Cannot cancel ACTIVE rentals - use early return instead
     */
    public function cancel(string $reason): void
    {
        if (!$this->status->canCancel()) {
            throw new \DomainException(
                'Cannot cancel a rental that is active or already completed. Use early return for active rentals.',
            );
        }

        $this->status = RentalStatus::CANCELLED;
        $this->cancellationReason = $reason;
        $this->depositStatus = DepositStatus::RELEASED;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Update return condition for a rental item
     *
     * @param array<int, string> $damagePhotos
     */
    public function updateItemReturnCondition(
        string $bikeId,
        string $condition,
        ?string $damageDescription = null,
        array $damagePhotos = [],
    ): void {
        foreach ($this->items as $item) {
            if ($item->bikeId() === $bikeId) {
                $item->recordReturn($condition, $damageDescription, $damagePhotos);

                return;
            }
        }

        throw new \DomainException("Bike {$bikeId} not found in rental");
    }

    public function addItem(RentalItem $item): void
    {
        $this->items[] = $item;
        $this->recalculateTotalAmount();
    }

    public function addEquipment(RentalEquipment $equipment): void
    {
        $this->equipments[] = $equipment;
        $this->recalculateTotalAmount();
    }
}
