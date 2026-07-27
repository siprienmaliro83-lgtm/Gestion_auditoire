<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammeAcademique extends Model
{
    use HasFactory;

    protected $table = 'programmes_academiques';

    protected $fillable = ['annee_academique_id', 'code', 'nom', 'description'];

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'programme_promotion')->withTimestamps();
    }

    public function ues(): HasMany
    {
        return $this->hasMany(Ue::class);
    }
}
