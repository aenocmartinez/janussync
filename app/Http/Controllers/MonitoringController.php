<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        // Lógica para obtener datos necesarios para la vista
        return view('monitoring.index');
    }
}
