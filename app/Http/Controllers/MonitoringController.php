<?php

namespace App\Http\Controllers;

use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\ScheduledTask;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    public function index()
    {   
        // Datos tabla de ejecuciones
        $executions = ScheduledTask::getAllLastExecutions();

        // Estadísticas (Gráficos)
        $executionStats = ScheduledTask::countLastExecutionResults();

        return view('monitoring.index', [
            'executions' => $executions,
            'executionStats' => $executionStats,
        ]);
    }

    public function checkConnectionStatus(Request $request)
    {
        try {
            $academusoftConnectionStatus = Academusoft::validatedConnection();
            $brightspaceConnectionStatus = BrightSpace::validatedConnection(); 
            
            return response()->json([
                'academusoft' => $academusoftConnectionStatus,
                'brightspace' => $brightspaceConnectionStatus,
            ]);
        } catch (Exception $e) {            
            Log::error('Error checking connection status: ' . $e->getMessage());
            return response()->json(['error' => 'Error checking connection status'], 500);
        }
    }

}
