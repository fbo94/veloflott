<?php

declare(strict_types=1);

namespace Fleet\Application\ListModels;

use Fleet\Domain\Model;

final readonly class ModelDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brandId,
        public ?string $brandName = null,
        public ?string $brandLogoUrl = null,
    ) {}

    public static function fromModel(Model $model, ?string $brandName = null, ?string $brandLogoUrl = null): self
    {
        return new self(
            id: $model->id(),
            name: $model->name(),
            brandId: $model->brandId(),
            brandName: $brandName,
            brandLogoUrl: $brandLogoUrl,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brandId,
            'brand_name' => $this->brandName,
            'brand_logo_url' => $this->brandLogoUrl,
        ];
    }
}
