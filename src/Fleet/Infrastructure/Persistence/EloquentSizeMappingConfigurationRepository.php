<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\SizeMappingConfiguration;
use Fleet\Domain\SizeMappingConfigurationRepositoryInterface;
use Fleet\Domain\SizeRange;
use Fleet\Infrastructure\Persistence\Models\SizeMappingConfigurationEloquentModel;

final readonly class EloquentSizeMappingConfigurationRepository implements SizeMappingConfigurationRepositoryInterface
{
    public function getActiveConfiguration(): ?SizeMappingConfiguration
    {
        $model = SizeMappingConfigurationEloquentModel::where('is_active', true)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(SizeMappingConfiguration $configuration): SizeMappingConfiguration
    {
        $model = SizeMappingConfigurationEloquentModel::find($configuration->id());

        if ($model === null) {
            $model = new SizeMappingConfigurationEloquentModel;
            $model->id = $configuration->id();
        }

        $model->version = $configuration->version();
        $model->is_active = $configuration->isActive();

        $model->xs_cm_min = $configuration->xsCm()->min();
        $model->xs_cm_max = $configuration->xsCm()->max();
        $model->xs_inch_min = $configuration->xsInch()->min();
        $model->xs_inch_max = $configuration->xsInch()->max();

        $model->s_cm_min = $configuration->sCm()->min();
        $model->s_cm_max = $configuration->sCm()->max();
        $model->s_inch_min = $configuration->sInch()->min();
        $model->s_inch_max = $configuration->sInch()->max();

        $model->m_cm_min = $configuration->mCm()->min();
        $model->m_cm_max = $configuration->mCm()->max();
        $model->m_inch_min = $configuration->mInch()->min();
        $model->m_inch_max = $configuration->mInch()->max();

        $model->l_cm_min = $configuration->lCm()->min();
        $model->l_cm_max = $configuration->lCm()->max();
        $model->l_inch_min = $configuration->lInch()->min();
        $model->l_inch_max = $configuration->lInch()->max();

        $model->xl_cm_min = $configuration->xlCm()->min();
        $model->xl_cm_max = $configuration->xlCm()->max();
        $model->xl_inch_min = $configuration->xlInch()->min();
        $model->xl_inch_max = $configuration->xlInch()->max();

        $model->xxl_cm_min = $configuration->xxlCm()->min();
        $model->xxl_cm_max = $configuration->xxlCm()->max();
        $model->xxl_inch_min = $configuration->xxlInch()->min();
        $model->xxl_inch_max = $configuration->xxlInch()->max();

        $model->save();

        return $configuration;
    }

    public function findById(string $id): ?SizeMappingConfiguration
    {
        $model = SizeMappingConfigurationEloquentModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function getNextVersion(): int
    {
        $maxVersion = SizeMappingConfigurationEloquentModel::max('version');

        return $maxVersion ? $maxVersion + 1 : 1;
    }

    private function toDomain(SizeMappingConfigurationEloquentModel $model): SizeMappingConfiguration
    {
        return new SizeMappingConfiguration(
            id: $model->id,
            version: $model->version,
            isActive: $model->is_active,
            xsCm: new SizeRange($model->xs_cm_min, $model->xs_cm_max),
            xsInch: new SizeRange($model->xs_inch_min, $model->xs_inch_max),
            sCm: new SizeRange($model->s_cm_min, $model->s_cm_max),
            sInch: new SizeRange($model->s_inch_min, $model->s_inch_max),
            mCm: new SizeRange($model->m_cm_min, $model->m_cm_max),
            mInch: new SizeRange($model->m_inch_min, $model->m_inch_max),
            lCm: new SizeRange($model->l_cm_min, $model->l_cm_max),
            lInch: new SizeRange($model->l_inch_min, $model->l_inch_max),
            xlCm: new SizeRange($model->xl_cm_min, $model->xl_cm_max),
            xlInch: new SizeRange($model->xl_inch_min, $model->xl_inch_max),
            xxlCm: new SizeRange($model->xxl_cm_min, $model->xxl_cm_max),
            xxlInch: new SizeRange($model->xxl_inch_min, $model->xxl_inch_max),
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
