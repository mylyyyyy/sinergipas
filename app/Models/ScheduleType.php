<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'shift_rules',
        'pattern',
        'uses_squads',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'shift_rules' => 'array',
        'pattern' => 'array',
        'uses_squads' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
