<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tunkin extends Model
{
    use HasFactory;

    protected $fillable = ['grade', 'nominal'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
