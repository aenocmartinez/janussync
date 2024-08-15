<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
}
