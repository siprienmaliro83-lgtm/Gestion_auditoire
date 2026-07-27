<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditoire extends Model
{
    use HasFactory;

    protected $fillable = ['batiment_id', 'nom', 'capacite', 'etat'];

    public function batiment(): BelongsTo
    {
        return $this->belongsTo(Batiment::class);
    }

    public function programmations(): HasMany
    {
        return $this->hasMany(Programmation::class);
    }
}
