<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'auditable_id',
        'auditable_type',
        'activity',
        'ip_address',
        'user_agent',
        'details',
        'is_system',
        'old_values',
        'new_values'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the parent auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
