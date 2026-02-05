<?php

declare(strict_types=1);

namespace Customer\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class CustomerEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'customers';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'identity_document_type',
        'identity_document_number',
        'height',
        'weight',
        'address',
        'notes',
        'photos',
        'is_risky',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_risky' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
