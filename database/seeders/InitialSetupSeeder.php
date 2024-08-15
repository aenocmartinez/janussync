<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialSetupSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'Crear usuario',
            'Ver usuario',
            'Actualizar usuario',
            'Eliminar usuario',
            'Crear rol',
            'Ver rol',
            'Actualizar rol',
            'Eliminar rol',
            'Hacer Monitoreo',
            'Hacer reintentos a ejecuciones fallidas',
            'Ver detalle de una ejecución exitosa',
            'Programar ejecución de tareas',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $adminRole->syncPermissions($permissions);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@unicolmayor.edu.co'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('4dm1n+2024'),
            ]
        );

        if (!$adminUser->hasRole('Administrador')) {
            $adminUser->assignRole($adminRole);
        }
    }
}
