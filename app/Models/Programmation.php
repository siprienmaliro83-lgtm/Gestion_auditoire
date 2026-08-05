<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Programmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_auditoire_id',
        'annee_academique_id',
        'ec_id',
        'enseignant_id',
        'auditoire_id',
        'promotions_concernees',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'effectif_total',
        'statut',
        'validee_par',
        'validee_a',
    ];

    protected function casts(): array
    {
        return [
            'promotions_concernees' => 'array',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'validee_a' => 'datetime',
        ];
    }

    public function demandeAuditoire(): BelongsTo
    {
        return $this->belongsTo(DemandeAuditoire::class);
    }

    public function anneeAcademique(): BelongsTo
    {
        return $this->belongsTo(AnneeAcademique::class);
    }

    public function ec(): BelongsTo
    {
        return $this->belongsTo(Ec::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function auditoire(): BelongsTo
    {
        return $this->belongsTo(Auditoire::class);
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

    public function getPromotionsNomAttribute(): string
    {
        return Promotion::whereIn('id', $this->promotions_concernees ?? [])
            ->orderBy('nom')
            ->pluck('nom')
            ->implode(', ');
    }
}
