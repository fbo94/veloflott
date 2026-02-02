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
        'is_risky',
    ];

    protected $casts = [
        'is_risky' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
