<?php

declare(strict_types=1);

namespace Rental\Application\ListActiveRentals;

use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;

final readonly class ActiveRentalDto
{
    /**
     * @param string[] $bikes
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $customerName,
        public string $startDate,
        public string $expectedReturnDate,
        public array $bikes,
        public string $status,
        public bool $isLate,
        public int $delayHours,
    ) {
    }

    public static function fromEloquentModel(RentalEloquentModel $model): self
    {
        $now = new \DateTimeImmutable();
        $expectedReturn = \DateTimeImmutable::createFromInterface($model->expected_return_date);
        $isLate = $now > $expectedReturn;
        $delayHours = $isLate
            ? (int) (($now->getTimestamp() - $expectedReturn->getTimestamp()) / 3600)
            : 0;

        $bikes = $model->items->map(function ($item) {
            $bike = $item->bike;
            return "{$bike->model->brand->name} {$bike->model->name} ({$bike->internal_number})";
        })->all();

        return new self(
            id: $model->id,
            customerId: $model->customer_id,
            customerName: "{$model->customer->first_name} {$model->customer->last_name}",
            startDate: $model->start_date->format('Y-m-d H:i'),
            expectedReturnDate: $model->expected_return_date->format('Y-m-d H:i'),
            bikes: $bikes,
            status: $model->status,
            isLate: $isLate,
            delayHours: $delayHours,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'start_date' => $this->startDate,
            'expected_return_date' => $this->expectedReturnDate,
            'bikes' => $this->bikes,
            'status' => $this->status,
            'is_late' => $this->isLate,
            'delay_hours' => $this->delayHours,
            'delay_indicator' => $this->getDelayIndicator(),
        ];
    }

    private function getDelayIndicator(): string
    {
        if (!$this->isLate) {
            $now = new \DateTimeImmutable();
            $expected = new \DateTimeImmutable($this->expectedReturnDate);
            $hoursRemaining = ($expected->getTimestamp() - $now->getTimestamp()) / 3600;

            if ($hoursRemaining <= 2) {
                return 'soon_late'; // Bientôt en retard
            }
            return 'on_time'; // Dans les temps
        }

        return 'late'; // En retard
    }
}
