<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateSizeMappingConfiguration;

use Fleet\Domain\SizeRange;

final readonly class UpdateSizeMappingConfigurationCommand
{
    public function __construct(
        public SizeRange $xsCm,
        public SizeRange $xsInch,
        public SizeRange $sCm,
        public SizeRange $sInch,
        public SizeRange $mCm,
        public SizeRange $mInch,
        public SizeRange $lCm,
        public SizeRange $lInch,
        public SizeRange $xlCm,
        public SizeRange $xlInch,
        public SizeRange $xxlCm,
        public SizeRange $xxlInch,
    ) {}
}
