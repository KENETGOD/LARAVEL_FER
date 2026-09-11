<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('admin', 'api');
        Role::findOrCreate('cliente', 'api');
        Role::findOrCreate('empleado', 'api');
    }
}
