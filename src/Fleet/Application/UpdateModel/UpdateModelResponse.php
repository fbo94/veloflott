<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateModel;

final readonly class UpdateModelResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brandId,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brandId,
        ];
    }
}
