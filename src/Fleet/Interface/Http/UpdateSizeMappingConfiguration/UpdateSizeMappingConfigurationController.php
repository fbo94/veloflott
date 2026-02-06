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
            $command = new UpdateSizeMappingConfigurationCommand(
                xsCm: new SizeRange($request->input('xs_cm.min'), $request->input('xs_cm.max')),
                xsInch: new SizeRange($request->input('xs_inch.min'), $request->input('xs_inch.max')),
                sCm: new SizeRange($request->input('s_cm.min'), $request->input('s_cm.max')),
                sInch: new SizeRange($request->input('s_inch.min'), $request->input('s_inch.max')),
                mCm: new SizeRange($request->input('m_cm.min'), $request->input('m_cm.max')),
                mInch: new SizeRange($request->input('m_inch.min'), $request->input('m_inch.max')),
                lCm: new SizeRange($request->input('l_cm.min'), $request->input('l_cm.max')),
                lInch: new SizeRange($request->input('l_inch.min'), $request->input('l_inch.max')),
                xlCm: new SizeRange($request->input('xl_cm.min'), $request->input('xl_cm.max')),
                xlInch: new SizeRange($request->input('xl_inch.min'), $request->input('xl_inch.max')),
                xxlCm: new SizeRange($request->input('xxl_cm.min'), $request->input('xxl_cm.max')),
                xxlInch: new SizeRange($request->input('xxl_inch.min'), $request->input('xxl_inch.max')),
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
