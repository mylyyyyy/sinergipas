<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'nik',
        'full_name',
        'phone_number',
        'position',
        'rank',
        'rank_id',
        'rank_class',
        'employee_type',
        'picket_regu',
        'photo',
        'position_id',
        'work_unit_id',
        'squad_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rank_relation(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    public function position_relation(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function work_unit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class, 'work_unit_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function audit_logs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function getPhotoAttribute($value)
    {
        if (!$value) return null;
        if (str_starts_with($value, 'data:image')) return $value;
        return '/storage/' . $value;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getIsReguAttribute()
    {
        $pos = strtoupper((string)$this->position);
        return str_contains($pos, 'JAGA') || str_contains($pos, 'PENJAGA') || $this->squad_id != null;
    }

    public function getCategoryLabelAttribute()
    {
        return $this->is_regu ? 'Petugas Jaga' : 'Staf';
    }

    public function getWhatsAppLinkAttribute()
    {
        $number = preg_replace('/[^0-9]/', '', $this->phone_number ?: '');
        if (empty($number)) return '#';
        if (str_starts_with($number, '0')) $number = '62' . substr($number, 1);
        return "https://wa.me/{$number}";
    }
}
