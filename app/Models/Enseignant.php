<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'nom',
        'prenom',
        'email',
        'telephone',
        'grade',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ecs(): BelongsToMany
    {
        return $this->belongsToMany(Ec::class, 'enseignant_ec')->withTimestamps();
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
