<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ec extends Model
{
    use HasFactory;

    protected $fillable = ['ue_id', 'code', 'nom', 'volume_horaire', 'statut'];

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_ec')->withTimestamps();
    }

    public function demandesAuditoire(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class);
    }

    public function programmations(): HasMany
    {
        return $this->hasMany(Programmation::class);
    }
}
