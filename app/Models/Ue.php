<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ue extends Model
{
    use HasFactory;

    protected $fillable = ['programme_academique_id', 'code', 'nom', 'credits', 'volume_horaire'];

    public function programmeAcademique(): BelongsTo
    {
        return $this->belongsTo(ProgrammeAcademique::class);
    }

    public function ecs(): HasMany
    {
        return $this->hasMany(Ec::class);
    }
}
