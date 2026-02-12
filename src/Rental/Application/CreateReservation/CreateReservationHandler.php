<?php

declare(strict_types=1);

namespace Rental\Application\CreateReservation;

use Customer\Domain\CustomerRepositoryInterface;
use DateTimeImmutable;
use Fleet\Domain\BikeRepositoryInterface;
use Illuminate\Support\Str;
use Pricing\Domain\PriceCalculation;
use Pricing\Domain\Services\PricingCalculator;
use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Rental\Application\CreateRental\BikeNotFoundException;
use Rental\Application\CreateRental\CustomerNotFoundException;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalEquipment;
use Rental\Domain\RentalItem;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;

final class CreateReservationHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly CustomerRepositoryInterface $customers,
        private readonly BikeRepositoryInterface $bikes,
        private readonly BikeAvailabilityServiceInterface $availabilityService,
        private readonly RentalSettingsRepositoryInterface $settingsRepository,
        private readonly PricingCalculator $pricingCalculator,
        private readonly DurationDefinitionRepositoryInterface $durationRepository,
    ) {
    }

    public function handle(CreateReservationCommand $command): CreateReservationResponse
    {
        // 1. Verify customer exists
        $customer = $this->customers->findById($command->customerId);
        if ($customer === null) {
            throw new CustomerNotFoundException($command->customerId);
        }

        // 2. Calculate expected return date
        $expectedReturnDate = $this->calculateExpectedReturnDate(
            $command->startDate,
            $command->duration,
            $command->customEndDate,
        );

        // 3. Validate reservation constraints
        $this->validateReservationConstraints(
            $command->startDate,
            $expectedReturnDate,
            $command->tenantId,
            $command->siteId,
        );

        // 4. Verify bike availability and calculate pricing
        $rentalItems = [];
        $rentalId = Str::uuid()->toString();
        $totalDiscountAmount = 0.0;

        // Calculer le nombre de jours pour le pricing
        $numberOfDays = (int) ceil($command->startDate->diff($expectedReturnDate)->days);
        if ($numberOfDays < 1) {
            $numberOfDays = 1;
        }

        foreach ($command->bikeItems as $bikeItemData) {
            $bike = $this->bikes->findById($bikeItemData->bikeId);
            if ($bike === null) {
                throw new BikeNotFoundException($bikeItemData->bikeId);
            }

            // Check calendar availability (not physical status)
            $availability = $this->availabilityService->isAvailableForPeriod(
                $bikeItemData->bikeId,
                $command->startDate,
                $expectedReturnDate,
            );

            if (!$availability->isAvailable) {
                throw new BikeNotAvailableForPeriodException(
                    $bikeItemData->bikeId,
                    $command->startDate,
                    $expectedReturnDate,
                    $availability->reason ?? 'Bike not available for this period',
                );
            }

            // Calculer le prix complet via le PricingCalculator (avec réductions)
            $priceCalculation = $this->calculatePriceForBike(
                $bike,
                $command->duration,
                $numberOfDays,
            );

            // Accumuler les réductions pour chaque vélo (× quantité)
            $bikeDiscount = ($priceCalculation->basePrice - $priceCalculation->finalPrice) * $bikeItemData->quantity;
            $totalDiscountAmount += $bikeDiscount;

            // Utiliser le prix par jour basé sur le prix de BASE (avant réductions)
            // La réduction sera appliquée globalement sur la location
            $dailyRate = $priceCalculation->basePrice / $numberOfDays;

            $rentalItems[] = new RentalItem(
                id: Str::uuid()->toString(),
                rentalId: $rentalId,
                bikeId: $bikeItemData->bikeId,
                dailyRate: $dailyRate,
                quantity: $bikeItemData->quantity,
            );
        }

        // 5. Create equipments
        $equipments = [];
        foreach ($command->equipmentItems as $equipmentData) {
            $equipments[] = new RentalEquipment(
                id: Str::uuid()->toString(),
                rentalId: $rentalId,
                type: $equipmentData->type,
                quantity: $equipmentData->quantity,
                pricePerUnit: $equipmentData->pricePerUnit,
            );
        }

        // 6. Determine initial status: RESERVED for future, PENDING for today
        $now = new DateTimeImmutable();
        $isToday = $command->startDate->format('Y-m-d') === $now->format('Y-m-d');
        $status = $isToday ? RentalStatus::PENDING : RentalStatus::RESERVED;

        // 7. Create the rental avec le montant des réductions calculées
        $rental = new Rental(
            id: $rentalId,
            customerId: $command->customerId,
            startDate: $command->startDate,
            expectedReturnDate: $expectedReturnDate,
            actualReturnDate: null,
            duration: $command->duration,
            depositAmount: $command->depositAmount,
            totalAmount: 0.0,
            discountAmount: $totalDiscountAmount, // Réductions calculées par PricingCalculator
            taxRate: 20.0,
            taxAmount: 0.0,
            totalWithTax: 0.0,
            status: $status,
            items: $rentalItems,
            equipments: $equipments,
            depositStatus: null, // Will be set to HELD by constructor
            depositRetained: null,
            cancellationReason: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        // 8. Recalculate total amount (will use the discountAmount we set)
        $rental->recalculateTotalAmount();

        // 9. Save the rental (NO bike status change here!)
        // Bikes remain AVAILABLE, only calendar dates are blocked
        $this->rentals->saveWithItems($rental);

        return CreateReservationResponse::fromRental($rental, $customer, $status);
    }

    private function calculateExpectedReturnDate(
        DateTimeImmutable $startDate,
        RentalDuration $duration,
        ?DateTimeImmutable $customEndDate,
    ): DateTimeImmutable {
        // Si une date de fin personnalisée est fournie, l'utiliser en priorité
        if ($customEndDate !== null) {
            return $customEndDate;
        }

        // Pour CUSTOM sans customEndDate, erreur
        if ($duration === RentalDuration::CUSTOM) {
            throw new \DomainException('Custom duration requires a custom end date');
        }

        // Pour les durées en jours complets, on calcule la date de fin à 23:59:59
        // Ex: TWO_DAYS = du 12 au 13 → retour le 13 à 23:59:59 → +1 jour
        if (in_array($duration, [RentalDuration::TWO_DAYS, RentalDuration::THREE_DAYS, RentalDuration::WEEK], true)) {
            $days = (int) $duration->days();
            $daysToAdd = $days - 1; // Le dernier jour se termine à 23:59:59

            return $startDate
                ->modify("+{$daysToAdd} days")
                ->setTime(23, 59, 59);
        }

        // Pour les durées en heures (HALF_DAY, FULL_DAY), on ajoute les heures
        $hours = $duration->hours();

        return $startDate->modify("+{$hours} hours");
    }

    private function validateReservationConstraints(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $tenantId,
        ?string $siteId,
    ): void {
        $settings = $this->settingsRepository->getEffectiveSettings($tenantId, $siteId);
        $now = new DateTimeImmutable();

        // Check minimum advance time
        $minStartDate = $now->modify("+{$settings->minReservationHoursAhead()} hours");
        if ($startDate < $now && $startDate->format('Y-m-d') !== $now->format('Y-m-d')) {
            throw new \DomainException('Reservation start date cannot be in the past');
        }

        // Check maximum duration
        $durationDays = (int) $startDate->diff($endDate)->days;
        if ($durationDays > $settings->maxRentalDurationDays()) {
            throw new \DomainException(
                "Rental duration cannot exceed {$settings->maxRentalDurationDays()} days",
            );
        }
    }

    /**
     * Calcule le prix complet pour un vélo via le PricingCalculator.
     * Retourne le PriceCalculation complet avec basePrice, finalPrice et réductions.
     */
    private function calculatePriceForBike(
        \Fleet\Domain\Bike $bike,
        RentalDuration $duration,
        int $numberOfDays,
    ): PriceCalculation {
        // Trouver la duration definition correspondante
        $durationId = $this->findDurationIdForRentalDuration($duration, $numberOfDays);

        if ($durationId === null) {
            throw new \DomainException(
                "No duration definition found for duration: {$duration->value}"
            );
        }

        // Récupérer le pricingClassId du vélo, ou utiliser la classe par défaut
        $pricingClassId = $bike->pricingClass()?->id();
        if ($pricingClassId === null) {
            $pricingClassId = $this->findDefaultPricingClassId();
        }

        // Calculer le prix via le PricingCalculator
        return $this->pricingCalculator->calculate(
            categoryId: $bike->categoryId(),
            pricingClassId: $pricingClassId,
            durationId: $durationId,
            customDays: $numberOfDays,
        );
    }

    private function findDefaultPricingClassId(): string
    {
        return 'default';
    }

    private function findDurationIdForRentalDuration(RentalDuration $duration, int $numberOfDays): ?string
    {
        $durations = $this->durationRepository->findAll();

        // Pour CUSTOM, trouver la durée qui correspond le mieux au nombre de jours
        if ($duration === RentalDuration::CUSTOM) {
            return $this->findBestMatchingDurationId($durations, $numberOfDays);
        }

        // Mapper RentalDuration vers DurationDefinition code
        $durationCode = match ($duration) {
            RentalDuration::HALF_DAY => 'half_day',
            RentalDuration::FULL_DAY => 'full_day',
            RentalDuration::TWO_DAYS => 'two_days',
            RentalDuration::THREE_DAYS => 'three_days',
            RentalDuration::WEEK => 'week',
            RentalDuration::CUSTOM => 'custom',
        };

        // Chercher la duration par code
        foreach ($durations as $durationDef) {
            if ($durationDef->code() === $durationCode) {
                return $durationDef->id();
            }
        }

        return null;
    }

    /**
     * Trouve la durée qui correspond le mieux au nombre de jours pour CUSTOM.
     * Priorité : durée exacte > durée supérieure la plus proche > durée inférieure la plus proche
     */
    private function findBestMatchingDurationId(array $durations, int $numberOfDays): ?string
    {
        $bestMatch = null;
        $bestDiff = PHP_INT_MAX;

        // Codes de durées prioritaires (du plus petit au plus grand)
        $priorityOrder = ['half_day', 'full_day', 'two_days', 'three_days', 'week', 'two_weeks', 'month'];

        foreach ($durations as $durationDef) {
            // Ignorer les durées "custom" car elles n'ont généralement pas de tarifs
            if ($durationDef->code() === 'custom') {
                continue;
            }

            $durationDays = $durationDef->durationDays();
            if ($durationDays === null) {
                continue;
            }

            $diff = abs($numberOfDays - $durationDays);

            // Si durée exacte, la prendre
            if ($diff === 0) {
                return $durationDef->id();
            }

            // Sinon, chercher la durée la plus proche (préférer celle qui est >= numberOfDays)
            if ($diff < $bestDiff || ($diff === $bestDiff && $durationDays >= $numberOfDays)) {
                $bestDiff = $diff;
                $bestMatch = $durationDef->id();
            }
        }

        return $bestMatch;
    }
}
