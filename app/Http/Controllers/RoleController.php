<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveRole;
use App\Http\Requests\UpdateRole;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleController extends Controller
{
    public function index(Request $request) {

        // $permission1 = Permission::create(['name' => 'Crear usuario']);
        // $permission2 = Permission::create(['name' => 'Eliminar usuario']);
        // $permission3 = Permission::create(['name' => 'Actualizar usuario']);
        // $permission4 = Permission::create(['name' => 'Lista de usuarios']);
        // $permission5 = Permission::create(['name' => 'Ver información de un usuario']);
        // $permission6 = Permission::create(['name' => 'Monitoreo de tareas']);
        // $permission7 = Permission::create(['name' => 'Programar ejecución de tareas']);
        // $permission8 = Permission::create(['name' => 'Crear rol']);
        // $permission9 = Permission::create(['name' => 'Eliminar rol']);
        // $permission10 = Permission::create(['name' => 'Actualizar rol']);
        // $permission11 = Permission::create(['name' => 'Lista de roles']);
        // $permission12 = Permission::create(['name' => 'Ver información de un rol']);
        // $permission13 = Permission::create(['name' => 'Reportes']);

        // $role = Role::find(1);
        // $role->givePermissionTo($permission1);
        // $role->givePermissionTo($permission2);
        // $role->givePermissionTo($permission3);
        // $role->givePermissionTo($permission4);
        // $role->givePermissionTo($permission5);
        // $role->givePermissionTo($permission6);
        // $role->givePermissionTo($permission7);
        // $role->givePermissionTo($permission8);
        // $role->givePermissionTo($permission9);
        // $role->givePermissionTo($permission10);
        // $role->givePermissionTo($permission11);
        // $role->givePermissionTo($permission12);
        // $role->givePermissionTo($permission13);
        // $role = Role::create(['name' => 'Administrador']);

        // $user = User::find(1);
        // $user->assignRole($role);

        // $role = Role::create(['name' => 'Monitoreo']);

        // $roles = [
        //     ['name' => 'Administrador', 'id' => '1'],
        //     ['name' => 'Monitoreo', 'id' => '2'],
        // ];

        $search = $request->input('search');

        $roles = Role::when($search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->paginate(5);
        return view('roles.index', [ 'roles' => $roles]);
    }

    public function create() {
        $permissions = Permission::orderBy('name')->get();
        
        return view('roles.create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(SaveRole $requestSaveRole) {
        $request = $requestSaveRole->validated();

        $role = Role::create(['name' => $request['name']]);
        if (!empty($request['permissions'])) {
            foreach($request['permissions'] as $permissionID) {
                $permission = Permission::findById($permissionID);
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        return redirect()->route('roles.index')->with('success', 'El rol se ha creado exitosamente.');
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            dd("Entra aqui");
            return redirect()->route('roles.index')->with('error', 'El rol no existe.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('warning', 'El rol no se puede eliminar porque tiene usuarios asociados.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'El rol ha sido eliminado exitosamente.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRole $requestUpdateRole, $id)
    {
        $validatedData = $requestUpdateRole->validated();
        try {
            $role = Role::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('roles.index')->with('error', 'El rol no existe.');
        }

        $role->name = $validatedData['name'];
        $role->save();

        $role->permissions()->sync($validatedData['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'El rol ha sido actualizado exitosamente.');
    }

}
