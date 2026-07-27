<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = ['mention_id', 'code', 'nom', 'niveau', 'effectif'];

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function programmesAcademiques(): BelongsToMany
    {
        return $this->belongsToMany(ProgrammeAcademique::class, 'programme_promotion')->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
