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
