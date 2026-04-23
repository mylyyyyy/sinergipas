<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Squad extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'description', 'schedule_type_id'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function scheduleType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ScheduleType::class);
    }
}
