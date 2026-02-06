<?php

declare(strict_types=1);

namespace Rental\Application\GetRentalDetail;

use Rental\Domain\Rental;

final readonly class GetRentalDetailResponse
{
    /**
     * @param  RentalItemDto[]  $items
     * @param  RentalEquipmentDto[]  $equipments
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $customerFirstName,
        public string $customerLastName,
        public string $customerEmail,
        public ?string $customerPhone,
        public string $startDate,
        public string $expectedReturnDate,
        public ?string $actualReturnDate,
        public string $duration,
        public float $numberOfDays,
        public float $depositAmount,
        public float $itemsSubtotal,
        public float $equipmentsSubtotal,
        public float $totalAmount,
        public float $discountAmount,
        public float $taxRate,
        public float $taxAmount,
        public float $totalWithTax,
        public string $status,
        public string $depositStatus,
        public ?float $depositRetained,
        public ?string $cancellationReason,
        public array $items,
        public array $equipments,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromRental(Rental $rental, array $customerData, array $items, array $equipments, float $numberOfDays): self
    {
        // Calculer les sous-totaux
        $itemsSubtotal = array_reduce($items, fn ($sum, $item) => $sum + $item->totalAmount, 0.0);
        $equipmentsSubtotal = array_reduce($equipments, fn ($sum, $equipment) => $sum + $equipment->totalAmount, 0.0);

        return new self(
            id: $rental->id(),
            customerId: $rental->customerId(),
            customerFirstName: $customerData['first_name'],
            customerLastName: $customerData['last_name'],
            customerEmail: $customerData['email'],
            customerPhone: $customerData['phone'],
            startDate: $rental->startDate()->format('Y-m-d H:i'),
            expectedReturnDate: $rental->expectedReturnDate()->format('Y-m-d H:i'),
            actualReturnDate: $rental->actualReturnDate()?->format('Y-m-d H:i'),
            duration: $rental->duration()->value,
            numberOfDays: $numberOfDays,
            depositAmount: $rental->depositAmount(),
            itemsSubtotal: $itemsSubtotal,
            equipmentsSubtotal: $equipmentsSubtotal,
            totalAmount: $rental->totalAmount(),
            discountAmount: $rental->discountAmount(),
            taxRate: $rental->taxRate(),
            taxAmount: $rental->taxAmount(),
            totalWithTax: $rental->totalWithTax(),
            status: $rental->status()->value,
            depositStatus: $rental->depositStatus()->value,
            depositRetained: $rental->depositRetained(),
            cancellationReason: $rental->cancellationReason(),
            items: $items,
            equipments: $equipments,
            createdAt: $rental->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $rental->updatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer' => [
                'id' => $this->customerId,
                'first_name' => $this->customerFirstName,
                'last_name' => $this->customerLastName,
                'full_name' => $this->customerFirstName.' '.$this->customerLastName,
                'email' => $this->customerEmail,
                'phone' => $this->customerPhone,
            ],
            'start_date' => $this->startDate,
            'expected_return_date' => $this->expectedReturnDate,
            'actual_return_date' => $this->actualReturnDate,
            'duration' => $this->duration,
            'number_of_days' => $this->numberOfDays,
            'status' => $this->status,
            'items' => array_map(fn ($item) => $item->toArray(), $this->items),
            'equipments' => array_map(fn ($equipment) => $equipment->toArray(), $this->equipments),
            'pricing' => [
                'items_subtotal' => $this->itemsSubtotal,
                'equipments_subtotal' => $this->equipmentsSubtotal,
                'subtotal' => $this->itemsSubtotal + $this->equipmentsSubtotal,
                'discount_amount' => $this->discountAmount,
                'subtotal_after_discount' => ($this->itemsSubtotal + $this->equipmentsSubtotal) - $this->discountAmount,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'total_with_tax' => $this->totalWithTax,
                'total_amount' => $this->totalAmount,
                'deposit_amount' => $this->depositAmount,
                'deposit_status' => $this->depositStatus,
                'deposit_retained' => $this->depositRetained,
            ],
            'cancellation_reason' => $this->cancellationReason,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
