<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batiment extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'nom', 'localisation', 'description'];

    public function auditoires(): HasMany
    {
        return $this->hasMany(Auditoire::class);
    }
}
