<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuario panel /admin: por defecto usuario "admin" y contraseña "admin".
 * Opcional en .env: ADMIN_USERNAME, ADMIN_PASSWORD, ADMIN_EMAIL (correo interno único)
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD', 'admin');
        $name = env('ADMIN_NAME', 'Administrador');
        $email = env('ADMIN_EMAIL', 'admin@local');

        // Evitar duplicados si antes existía solo login por correo
        User::where('email', 'admin@diariozonasur.cl')->delete();

        User::updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]
        );
    }
}
