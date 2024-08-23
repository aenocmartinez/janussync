<?php

namespace App\Http\Controllers;

use App\Models\Academusoft;
use App\Models\BrightSpace;
use App\Models\CourseCreationDetail;
use App\Models\GradeCreateDetail;
use App\Models\ScheduledTask;
use App\Models\UserCreationDetail;
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

    public function showDetail($task_id, $action)
    {
        $scheduledTask = ScheduledTask::findOrFail($task_id);
    
        $actions = config('scheduled_task_actions.actions');
    
        if (!isset($actions[$action])) {
            abort(404, 'Acción no reconocida');
        }
    
        // Verificar que la acción implementa la interfaz HasModel
        if (!in_array('App\Contracts\HasModel', class_implements($action))) {
            abort(500, 'La acción no implementa la interfaz HasModel');
        }
    
        // Obtener el modelo desde la clase Action
        $modelClass = $action::getModelClass();
    
        if (!class_exists($modelClass)) {
            abort(404, 'Modelo no encontrado para la acción: ' . $action);
        }
    
        // Obtener los detalles paginados y ordenados
        $details = $modelClass::where('scheduled_task_id', $task_id)
                    ->with('logTasks') // Cargar los logs asociados
                    ->orderBy('created_at', 'desc')  
                    ->paginate(10);
    
        // Obtener el nombre de la vista partial desde el modelo
        $view = $modelClass::getPartialViewName();
    
        return view('monitoring.detail', compact('details', 'view', 'scheduledTask'));
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

    public function executeScheduledTasks()
    {
        ScheduledTask::checkAndRunScheduledTasks();
        return response()->json(['message' => 'Tareas programadas ejecutadas.']);
    }    

    public function runTaskById($id)
    {
        $task = ScheduledTask::findOrFail($id);
        $task->executeImmediately();

        return response()->json(['message' => 'Tarea programada ejecutada con éxito.', 'task_id' => $task->id]);
    }    
}
