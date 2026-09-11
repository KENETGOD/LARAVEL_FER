<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['email' => 'bbva@gmail.com', 'name' => 'BBVA', 'rol' => 'admin'],
            ['email' => 'admin@example.com', 'name' => 'Admin', 'rol' => 'admin'],
            ['email' => 'empleado@example.com', 'name' => 'Empleado', 'rol' => 'empleado'],
            ['email' => 'cliente@example.com', 'name' => 'Cliente', 'rol' => 'cliente'],
        ];

        foreach ($usuarios as $usuario) {
            $user = User::firstOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'password' => Hash::make('password'),
                ]
            );

            $user->assignRole($usuario['rol']);
        }
    }
}
