<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\Bike;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSize;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;
use Fleet\Domain\PricingTier;
use Fleet\Domain\RetirementReason;
use Fleet\Domain\UnavailabilityReason;
use Fleet\Domain\WheelSize;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

final class EloquentBikeRepository implements BikeRepositoryInterface
{
    public function findById(string $id): ?Bike
    {
        $model = BikeEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByQrCodeUuid(string $qrCodeUuid): ?Bike
    {
        $model = BikeEloquentModel::where('qr_code_uuid', $qrCodeUuid)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByInternalNumber(string $internalNumber): ?Bike
    {
        $model = BikeEloquentModel::where('internal_number', $internalNumber)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Bike[]
     */
    public function findAll(
        ?BikeStatus $status = null,
        ?string $categoryId = null,
        ?FrameSizeLetter $frameSize = null,
        bool $includeRetired = false,
    ): array {
        $query = BikeEloquentModel::query();

        if (! $includeRetired) {
            $query->where('status', '!=', 'retired');
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($frameSize !== null) {
            $query->where('frame_size_letter_equivalent', $frameSize->value);
        }

        return $query->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Bike[]
     */
    public function search(string $query): array
    {
        return BikeEloquentModel::where(function ($q) use ($query) {
            $q->where('internal_number', 'LIKE', "%{$query}%")
                ->orWhere('serial_number', 'LIKE', "%{$query}%")
                ->orWhereHas('model', function ($modelQuery) use ($query) {
                    $modelQuery->where('name', 'LIKE', "%{$query}%")
                        ->orWhereHas('brand', function ($brandQuery) use ($query) {
                            $brandQuery->where('name', 'LIKE', "%{$query}%");
                        });
                });
        })
            ->where('status', '!=', 'retired')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @param  string[]|null  $statuses
     * @param  string[]|null  $categoryIds
     * @param  string[]|null  $frameSizes
     * @return array{bikes: Bike[], total: int}
     */
    public function findFiltered(
        ?array $statuses = null,
        ?array $categoryIds = null,
        ?array $frameSizes = null,
        bool $includeRetired = false,
        ?string $search = null,
        string $sortBy = 'internal_number',
        string $sortDirection = 'asc',
        int $page = 1,
        int $perPage = 50,
    ): array {
        $query = BikeEloquentModel::query();

        // Exclure les vélos retirés par défaut
        if (! $includeRetired) {
            $query->where('status', '!=', 'retired');
        }

        // Filtrer par statuts multiples
        if ($statuses !== null && count($statuses) > 0) {
            $query->whereIn('status', $statuses);
        }

        // Filtrer par catégories multiples
        if ($categoryIds !== null && count($categoryIds) > 0) {
            $query->whereIn('category_id', $categoryIds);
        }

        // Filtrer par tailles de cadre multiples
        if ($frameSizes !== null && count($frameSizes) > 0) {
            $query->whereIn('frame_size_letter_equivalent', $frameSizes);
        }

        // Recherche textuelle
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('internal_number', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('model', function ($modelQuery) use ($search) {
                        $modelQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                $brandQuery->where('name', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }

        // Compter le total avant pagination
        $total = $query->count();

        // Appliquer le tri
        $allowedSortFields = ['internal_number', 'status', 'category_id', 'created_at', 'updated_at'];
        $sortField = in_array($sortBy, $allowedSortFields) ? $sortBy : 'internal_number';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDir);

        // Appliquer la pagination
        $offset = ($page - 1) * $perPage;
        $query->offset($offset)->limit($perPage);

        // Récupérer les résultats avec eager loading des relations
        $bikes = $query->with(['model.brand', 'category', 'pricingClass'])->get()->all();

        return [
            'bikes' => $bikes,
            'total' => $total,
        ];
    }

    public function countByCategoryId(string $categoryId): int
    {
        return BikeEloquentModel::where('category_id', $categoryId)->count();
    }

    public function findByIdWithRelations(string $id): ?BikeEloquentModel
    {
        return BikeEloquentModel::with(['model.brand', 'category', 'pricingClass'])->find($id);
    }

    public function countByStatus(): array
    {
        return BikeEloquentModel::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function countActive(): int
    {
        return BikeEloquentModel::where('status', '!=', 'retired')->count();
    }

    public function getAverageAge(): float
    {
        $currentYear = (int) date('Y');

        $averageYear = BikeEloquentModel::query()
            ->where('status', '!=', 'retired')
            ->whereNotNull('year')
            ->avg('year');

        if ($averageYear === null) {
            return 0.0;
        }

        return round($currentYear - $averageYear, 1);
    }

    public function findLongUnavailable(int $minDays = 5): array
    {
        $thresholdDate = now()->subDays($minDays);

        return BikeEloquentModel::query()
            ->where('status', BikeStatus::UNAVAILABLE->value)
            ->where('updated_at', '<=', $thresholdDate)
            ->get()
            ->map(function ($bike) {
                $daysUnavailable = now()->diffInDays($bike->updated_at);

                return [
                    'bike_id' => $bike->id,
                    'internal_number' => $bike->internal_number,
                    'days_unavailable' => $daysUnavailable,
                ];
            })
            ->all();
    }

    public function save(Bike $bike): void
    {
        BikeEloquentModel::updateOrCreate(
            ['id' => $bike->id()],
            [
                'qr_code_uuid' => $bike->qrCodeUuid(),
                'internal_number' => $bike->internalNumber(),
                'model_id' => $bike->modelId(),
                'category_id' => $bike->categoryId(),
                'frame_size_unit' => $bike->frameSize()->unit->value,
                'frame_size_numeric' => $bike->frameSize()->numericValue,
                'frame_size_letter' => $bike->frameSize()->letterValue?->value,
                'frame_size_letter_equivalent' => $bike->frameSize()->letterEquivalent->value,
                'status' => $bike->status()->value,
                'pricing_class_id' => $bike->pricingClass()?->id(),
                'year' => $bike->year(),
                'serial_number' => $bike->serialNumber(),
                'color' => $bike->color(),
                'wheel_size' => $bike->wheelSize()?->value,
                'front_suspension' => $bike->frontSuspension(),
                'rear_suspension' => $bike->rearSuspension(),
                'brake_type' => $bike->brakeType()?->value,
                'purchase_price' => $bike->purchasePrice(),
                'purchase_date' => $bike->purchaseDate(),
                'notes' => $bike->notes(),
                'photos' => $bike->photos(),
                'retirement_reason' => $bike->retirementReason()?->value,
                'retirement_comment' => $bike->retirementComment(),
                'retired_at' => $bike->retiredAt(),
                'unavailability_reason' => $bike->unavailabilityReason()?->value,
                'unavailability_comment' => $bike->unavailabilityComment(),
            ]
        );
    }

    private function toDomain(BikeEloquentModel $model): Bike
    {
        return new Bike(
            id: $model->id,
            qrCodeUuid: $model->qr_code_uuid,
            internalNumber: $model->internal_number,
            modelId: $model->model_id,
            categoryId: $model->category_id,
            frameSize: $this->mapFrameSize($model),
            status: BikeStatus::from($model->status),
            pricingTier: $model->pricing_tier ?? PricingTier::STANDARD,
            pricingClass: $this->mapPricingClass($model),
            year: $model->year,
            serialNumber: $model->serial_number,
            color: $model->color,
            wheelSize: $model->wheel_size !== null ? WheelSize::from($model->wheel_size) : null,
            frontSuspension: $model->front_suspension,
            rearSuspension: $model->rear_suspension,
            brakeType: $model->brake_type !== null ? BrakeType::from($model->brake_type) : null,
            purchasePrice: $model->purchase_price,
            purchaseDate: $model->purchase_date !== null ? \DateTimeImmutable::createFromInterface($model->purchase_date) : null,
            notes: $model->notes,
            photos: $model->photos ?? [],
            retirementReason: $model->retirement_reason !== null ? RetirementReason::from($model->retirement_reason) : null,
            retirementComment: $model->retirement_comment,
            retiredAt: $model->retired_at !== null ? \DateTimeImmutable::createFromInterface($model->retired_at) : null,
            unavailabilityReason: $model->unavailability_reason !== null ? UnavailabilityReason::from($model->unavailability_reason) : null,
            unavailabilityComment: $model->unavailability_comment,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: \DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    private function mapFrameSize(BikeEloquentModel $model): FrameSize
    {
        $unit = FrameSizeUnit::from($model->frame_size_unit);

        return match ($unit) {
            FrameSizeUnit::LETTER => FrameSize::fromLetter(
                FrameSizeLetter::from($model->frame_size_letter)
            ),
            FrameSizeUnit::CM => FrameSize::fromCentimeters($model->frame_size_numeric),
            FrameSizeUnit::INCH => FrameSize::fromInches($model->frame_size_numeric),
        };
    }

    private function mapPricingClass(BikeEloquentModel $model): ?PricingClass
    {
        $pricingClassModel = $model->pricingClass;

        if ($pricingClassModel === null) {
            return null;
        }

        return new PricingClass(
            id: $pricingClassModel->id,
            code: $pricingClassModel->code,
            label: $pricingClassModel->label,
            description: $pricingClassModel->description,
            color: $pricingClassModel->color,
            sortOrder: $pricingClassModel->sort_order,
            isActive: $pricingClassModel->is_active,
            deletedAt: $pricingClassModel->deleted_at !== null ? \DateTimeImmutable::createFromInterface($pricingClassModel->deleted_at) : null,
            createdAt: \DateTimeImmutable::createFromInterface($pricingClassModel->created_at),
            updatedAt: \DateTimeImmutable::createFromInterface($pricingClassModel->updated_at),
        );
    }
}
