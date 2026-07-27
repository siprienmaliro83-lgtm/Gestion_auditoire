<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::where('nom', 'Administrateur')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@universite.cd'],
            [
                'role_id' => $adminRoleId,
                'name' => 'Administrateur',
                'password' => 'password',
                'domaine_id' => null,
                'promotion_id' => null,
                'confirme' => true,
            ],
        );
    }
}
