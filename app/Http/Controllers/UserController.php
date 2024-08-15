<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException; 

class UserController extends Controller
{
    public function index() {
        $users = User::all();
        return view('users.index', [ 'users' => $users]);
    }

    public function create() {

        $roles = Role::all();
        return view('users.create', [
            'roles' => $roles,
            'user' => new User(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $dataValidated = $request->validated();
        
        $roleExists = Role::find($dataValidated['role']);
        if (!$roleExists) {
            return redirect()->back()->with('error', 'El rol seleccionado no existe.');
        }

        $user = User::create([
            'name' => $dataValidated['name'],
            'email' => $dataValidated['email'],
            'password' => Hash::make($dataValidated['password']),
        ]);

        $user->roles()->attach($dataValidated['role']);

        return redirect()->route('users.index')->with('success', 'El usuario ha sido creado exitosamente.');
    }

    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            $roles = Role::all();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('users.index')->with('error', 'El usuario que intentas editar no existe.');
        }
        return view('users.edit', [
            'user' => $user, 
            'roles' => $roles,
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $dataValidated = $request->validated();

            $user->name = $dataValidated['name'];
            $user->email = $dataValidated['email'];

            if (!empty($dataValidated['password'])) {
                $user->password = Hash::make($dataValidated['password']);
            }

            $user->save();
            
            $user->roles()->sync([$dataValidated['role']]);

        } catch (ModelNotFoundException $e) {

            return redirect()->route('users.index')->with('error', 'El usuario que intentas actualizar no existe.');
        } catch (\Exception $e) {

            return redirect()->route('users.index')->with('error', 'Ocurrió un error al intentar actualizar el usuario.');
        }

        return redirect()->route('users.index')->with('success', 'El usuario ha sido actualizado exitosamente.');        
    }
    
    
    
}