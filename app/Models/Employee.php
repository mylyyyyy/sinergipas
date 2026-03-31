<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'full_name',
        'position',
        'rank',
        'photo',
        'position_id',
        'work_unit_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position_relation(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function work_unit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class, 'work_unit_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
