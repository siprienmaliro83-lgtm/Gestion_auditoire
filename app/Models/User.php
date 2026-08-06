<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'confirme',
        'name',
        'email',
        'matricule',
        'password',
        'domaine_id',
        'filiere_id',
        'mention_id',
        'promotion_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'confirme' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function domaine(): BelongsTo
    {
        return $this->belongsTo(Domaine::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function mention(): BelongsTo
    {
        return $this->belongsTo(Mention::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function demandesAuditoire(): HasMany
    {
        return $this->hasMany(DemandeAuditoire::class);
    }

    public function programmationsValidees(): HasMany
    {
        return $this->hasMany(Programmation::class, 'validee_par');
    }

    public function enseignant(): HasOne
    {
        return $this->hasOne(Enseignant::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = array_map(
            fn (string $role): string => Str::lower(Str::ascii($role)),
            (array) $roles,
        );

        $userRole = $this->role?->nom;

        return $userRole !== null && in_array(Str::lower(Str::ascii($userRole)), $roles, true);
    }
}
