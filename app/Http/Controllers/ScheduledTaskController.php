<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduledTaskRequest;
use App\Models\ScheduledTask;
use Illuminate\Http\Request;

class ScheduledTaskController extends Controller
{
    public function index(Request $request) {
        $search = $request->input('search');
    
        $tasks = ScheduledTask::query()
                    ->when($search, function ($query, $search) {
                        return $query->where(function($query) use ($search) {
                            $query->where('task_name', 'like', '%' . $search . '%')
                                  ->orWhere('frequency', 'like', '%' . $search . '%');
                        });
                    })
                    ->orderBy('task_name') 
                    ->paginate(5);
            
        $tasks->getCollection()->transform(function ($task) {
            $formattedExecutionTime = \Carbon\Carbon::parse($task->execution_time)->format('H:i');
    
            if ($task->frequency == 'Diaria') {
                $task->execution_info = "Todos los días a las $formattedExecutionTime horas";
            } elseif ($task->frequency == 'Semanal') {
                $task->execution_info = "Cada {$task->day_of_week} a las $formattedExecutionTime horas";
            } elseif ($task->frequency == 'Mensual') {
                $task->execution_info = "Cada día {$task->day_of_month} del mes a las $formattedExecutionTime horas";
            } elseif ($task->frequency == 'Personalizada') {
                $formattedDate = \Carbon\Carbon::parse($task->custom_date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
                $task->execution_info = "El $formattedDate a las $formattedExecutionTime horas";
            }
    
            return $task;
        });
    
        return view('scheduled_tasks.index', ['tasks' => $tasks]);
    }
    

    public function create()
    {        
        return view('scheduled_tasks.create', [
            'scheduledTask' => new ScheduledTask()
        ]);
    }

    public function store(StoreScheduledTaskRequest $request)
    {
        $validated = $request->validated();

        if ($request->frequency == 'Diaria') {
            $validated['execution_time'] = $this->combineDateAndTime(now()->toDateString(), $validated['execution_time_daily']);
        } elseif ($request->frequency == 'Semanal') {
            $validated['execution_time'] = $this->combineDateAndTime(now()->toDateString(), $validated['execution_time_weekly']);
        } elseif ($request->frequency == 'Mensual') {
            $validated['execution_time'] = $this->combineDateAndTime(now()->toDateString(), $validated['execution_time_monthly']);
        } elseif ($request->frequency == 'Personalizada') {
            $validated['execution_time'] = $this->combineDateAndTime($validated['custom_date'], $validated['execution_time_custom']);
        }
    
        ScheduledTask::create($validated);
    
        return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada creada con éxito');
    }
    
    protected function combineDateAndTime($date, $time)
    {
        return $date . ' ' . $time;
    }
    

    public function edit(ScheduledTask $scheduledTask)
    {
        return view('scheduled_tasks.edit', compact('scheduledTask'));
    }

    public function update(Request $request, ScheduledTask $scheduledTask)
    {
        $validated = $request->validate([
            'task_name' => 'required|unique:scheduled_tasks,task_name,' . $scheduledTask->id,
            'frequency' => 'required',
            // Validaciones adicionales según la frecuencia
        ]);

        $scheduledTask->update($validated);
        return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada actualizada exitosamente.');
    }

    public function destroy(ScheduledTask $scheduledTask)
    {
        $scheduledTask->delete();
        return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada eliminada exitosamente.');
    }
}
