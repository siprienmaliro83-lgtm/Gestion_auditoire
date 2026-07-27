<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domaine extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'nom', 'description'];

    public function filieres(): HasMany
    {
        return $this->hasMany(Filiere::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
