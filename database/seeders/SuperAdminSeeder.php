<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public const EMAIL = 'admin@universite.cd';

    public const PASSWORD = 'Admin@123456';

    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['nom' => 'Super Administrateur'],
            ['description' => 'Administration totale : validation des comptes, paramétrage global et statistiques.'],
        );

        $user = User::firstOrCreate(
            ['email' => self::EMAIL],
            [
                'role_id' => $role->id,
                'name' => 'Super Administrateur',
                'password' => Hash::make(self::PASSWORD),
                'confirme' => true,
            ],
        );

        // Si le compte existait déjà (ex. ancien admin), on garantit qu'il
        // dispose bien du rôle et de l'activation attendus, sans doublon.
        if (! $user->wasRecentlyCreated) {
            $user->update([
                'role_id' => $role->id,
                'confirme' => true,
            ]);
        }
    }
}
