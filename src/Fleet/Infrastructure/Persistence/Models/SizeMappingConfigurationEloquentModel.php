<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string|null $tenant_id
 * @property int $version
 * @property bool $is_active
 * @property int $xs_cm_min
 * @property int $xs_cm_max
 * @property int $xs_inch_min
 * @property int $xs_inch_max
 * @property int $s_cm_min
 * @property int $s_cm_max
 * @property int $s_inch_min
 * @property int $s_inch_max
 * @property int $m_cm_min
 * @property int $m_cm_max
 * @property int $m_inch_min
 * @property int $m_inch_max
 * @property int $l_cm_min
 * @property int $l_cm_max
 * @property int $l_inch_min
 * @property int $l_inch_max
 * @property int $xl_cm_min
 * @property int $xl_cm_max
 * @property int $xl_inch_min
 * @property int $xl_inch_max
 * @property int $xxl_cm_min
 * @property int $xxl_cm_max
 * @property int $xxl_inch_min
 * @property int $xxl_inch_max
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
final class SizeMappingConfigurationEloquentModel extends Model
{
    protected $table = 'size_mapping_configurations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'version',
        'is_active',
        'xs_cm_min',
        'xs_cm_max',
        'xs_inch_min',
        'xs_inch_max',
        's_cm_min',
        's_cm_max',
        's_inch_min',
        's_inch_max',
        'm_cm_min',
        'm_cm_max',
        'm_inch_min',
        'm_inch_max',
        'l_cm_min',
        'l_cm_max',
        'l_inch_min',
        'l_inch_max',
        'xl_cm_min',
        'xl_cm_max',
        'xl_inch_min',
        'xl_inch_max',
        'xxl_cm_min',
        'xxl_cm_max',
        'xxl_inch_min',
        'xxl_inch_max',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
        'xs_cm_min' => 'integer',
        'xs_cm_max' => 'integer',
        'xs_inch_min' => 'integer',
        'xs_inch_max' => 'integer',
        's_cm_min' => 'integer',
        's_cm_max' => 'integer',
        's_inch_min' => 'integer',
        's_inch_max' => 'integer',
        'm_cm_min' => 'integer',
        'm_cm_max' => 'integer',
        'm_inch_min' => 'integer',
        'm_inch_max' => 'integer',
        'l_cm_min' => 'integer',
        'l_cm_max' => 'integer',
        'l_inch_min' => 'integer',
        'l_inch_max' => 'integer',
        'xl_cm_min' => 'integer',
        'xl_cm_max' => 'integer',
        'xl_inch_min' => 'integer',
        'xl_inch_max' => 'integer',
        'xxl_cm_min' => 'integer',
        'xxl_cm_max' => 'integer',
        'xxl_inch_min' => 'integer',
        'xxl_inch_max' => 'integer',
    ];
}
