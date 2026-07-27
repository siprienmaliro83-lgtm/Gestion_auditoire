<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DemandeAuditoire extends Model
{
    use HasFactory;

    protected $table = 'demandes_auditoire';

    protected $fillable = [
        'user_id',
        'ec_id',
        'enseignant_id',
        'promotions_concernees',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'effectif_total',
        'statut',
        'motif_refus',
        'envoyee_a',
    ];

    protected function casts(): array
    {
        return [
            'promotions_concernees' => 'array',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'envoyee_a' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ec(): BelongsTo
    {
        return $this->belongsTo(Ec::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function programmation(): HasOne
    {
        return $this->hasOne(Programmation::class);
    }
}
