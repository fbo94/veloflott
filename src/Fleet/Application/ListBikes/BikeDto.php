<?php

declare(strict_types=1);

namespace Fleet\Application\ListBikes;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

final readonly class BikeDto
{
    /**
     * @param  string[]  $photos
     */
    public function __construct(
        public string $id,
        public string $qrCodeUuid,
        public string $internalNumber,
        public ?string $serialNumber,
        public string $modelId,
        public string $brand,
        public string $brandId,
        public string $model,
        public string $status,
        public string $categoryId,
        public string $category,
        public array $frameSize,
        public ?int $year,
        public ?string $color,
        public array $photos,
        public ?string $pricingClassId,
        public ?string $pricingClassCode,
        public ?string $pricingClassLabel,
        public ?string $siteId,
        public ?string $siteName,
        public ?string $siteSlug,
        public ?string $siteStatus,
    ) {
    }

    public static function fromEloquentModel(BikeEloquentModel $bikeModel): self
    {
        return new self(
            id: $bikeModel->id,
            qrCodeUuid: $bikeModel->qr_code_uuid,
            internalNumber: $bikeModel->internal_number,
            serialNumber: $bikeModel->serial_number,
            modelId: $bikeModel->model_id,
            brand: $bikeModel->model->brand->name,
            brandId: $bikeModel->model->brand->id,
            model: $bikeModel->model->name,
            status: $bikeModel->status,
            categoryId: $bikeModel->category->id,
            category: $bikeModel->category->name,
            frameSize: [
                'unit' => $bikeModel->frame_size_unit,
                'numeric_value' => $bikeModel->frame_size_numeric,
                'letter_value' => $bikeModel->frame_size_letter,
                'letter_equivalent' => $bikeModel->frame_size_letter_equivalent,
            ],
            year: $bikeModel->year,
            color: $bikeModel->color,
            photos: $bikeModel->photos ?? [],
            pricingClassId: $bikeModel->pricingClass?->id,
            pricingClassCode: $bikeModel->pricingClass?->code,
            pricingClassLabel: $bikeModel->pricingClass?->label,
            siteId: $bikeModel->site?->id,
            siteName: $bikeModel->site?->name,
            siteSlug: $bikeModel->site?->slug,
            siteStatus: $bikeModel->site?->status,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'qr_code_uuid' => $this->qrCodeUuid,
            'internal_number' => $this->internalNumber,
            'serial_number' => $this->serialNumber,
            'brand' => [
                'id' => $this->brandId,
                'name' => $this->brand,
            ],
            'model' => [
                'id' => $this->modelId,
                'name' => $this->model,
            ],
            'category' => [
                'id' => $this->categoryId,
                'name' => $this->category,
            ],
            'status' => $this->status,
            'category_id' => $this->categoryId,
            'frame_size' => $this->frameSize,
            'year' => $this->year,
            'color' => $this->color,
            'photos' => $this->photos,
            'pricing_class' => $this->pricingClassId !== null ? [
                'id' => $this->pricingClassId,
                'code' => $this->pricingClassCode,
                'label' => $this->pricingClassLabel,
            ] : null,
            'site' => $this->siteId !== null ? [
                'id' => $this->siteId,
                'name' => $this->siteName,
                'slug' => $this->siteSlug,
                'status' => $this->siteStatus,
            ] : null,
        ];
    }
}
