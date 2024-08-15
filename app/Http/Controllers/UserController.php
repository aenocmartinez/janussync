<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index() {
        $users = User::all();
        

        // $users = [
        //     ['name' => 'María Fernanda García López', 'role' => 'Monitoreo', 'id' => '1'],
        //     ['name' => 'Juan Carlos Pérez García', 'role' => 'Administrador', 'id' => '2'],
        //     ['name' => 'Carlos Andrés Fernández Ortiz', 'role' => 'Monitoreo', 'id' => '3'],
        //     ['name' => 'Lucía María López Martínez', 'role' => 'Administrador', 'id' => '4'],
        //     ['name' => 'José Antonio Martínez Sánchez', 'role' => 'Monitoreo', 'id' => '5'],
        // ];
        return view('users.index', [ 'users' => $users]);
    }

    public function create() {

        $roles = Role::all();
        return view('users.create', [
            'roles' => $roles
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
}
