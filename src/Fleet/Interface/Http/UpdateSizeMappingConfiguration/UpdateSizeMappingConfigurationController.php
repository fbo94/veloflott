<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateSizeMappingConfiguration;

use Fleet\Application\UpdateSizeMappingConfiguration\UpdateSizeMappingConfigurationCommand;
use Fleet\Application\UpdateSizeMappingConfiguration\UpdateSizeMappingConfigurationHandler;
use Fleet\Domain\SizeRange;
use Illuminate\Http\JsonResponse;

final readonly class UpdateSizeMappingConfigurationController
{
    public function __construct(
        private UpdateSizeMappingConfigurationHandler $handler,
    ) {
    }

    public function __invoke(UpdateSizeMappingConfigurationRequest $request): JsonResponse
    {
        try {
            // Transformer le tableau sizes[] en map indexé par letter
            $sizesMap = collect($request->input('sizes'))
                ->keyBy('letter')
                ->toArray();

            $command = new UpdateSizeMappingConfigurationCommand(
                xsCm: new SizeRange($sizesMap['xs']['cm']['min'], $sizesMap['xs']['cm']['max']),
                xsInch: new SizeRange($sizesMap['xs']['inch']['min'], $sizesMap['xs']['inch']['max']),
                sCm: new SizeRange($sizesMap['s']['cm']['min'], $sizesMap['s']['cm']['max']),
                sInch: new SizeRange($sizesMap['s']['inch']['min'], $sizesMap['s']['inch']['max']),
                mCm: new SizeRange($sizesMap['m']['cm']['min'], $sizesMap['m']['cm']['max']),
                mInch: new SizeRange($sizesMap['m']['inch']['min'], $sizesMap['m']['inch']['max']),
                lCm: new SizeRange($sizesMap['l']['cm']['min'], $sizesMap['l']['cm']['max']),
                lInch: new SizeRange($sizesMap['l']['inch']['min'], $sizesMap['l']['inch']['max']),
                xlCm: new SizeRange($sizesMap['xl']['cm']['min'], $sizesMap['xl']['cm']['max']),
                xlInch: new SizeRange($sizesMap['xl']['inch']['min'], $sizesMap['xl']['inch']['max']),
                xxlCm: new SizeRange($sizesMap['xxl']['cm']['min'], $sizesMap['xxl']['cm']['max']),
                xxlInch: new SizeRange($sizesMap['xxl']['inch']['min'], $sizesMap['xxl']['inch']['max']),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 200);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
