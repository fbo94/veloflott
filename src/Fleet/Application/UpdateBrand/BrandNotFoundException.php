<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBrand;

final class BrandNotFoundException extends \Exception
{
    public function __construct(string $brandId)
    {
        parent::__construct("Brand with ID {$brandId} not found");
    }
}
