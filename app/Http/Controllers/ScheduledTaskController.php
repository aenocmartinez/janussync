<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\StoreScheduledTaskRequest;
use Illuminate\Database\QueryException;
use App\Models\ScheduledTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    
        try {
            if ($request->frequency == 'Diaria') {
                $validated['execution_time'] = $this->combineDateAndTime(now()->toDateString(), $validated['execution_time_daily']);
            } elseif ($request->frequency == 'Semanal') {
                $validated['execution_time'] = $this->combineDateAndTime(now()->toDateString(), $validated['execution_time_weekly']);
            } elseif ($request->frequency == 'Mensual') {
                $validated['execution_time'] = $this->combineDateAndTime(now()->toDateString(), $validated['execution_time_monthly']);
            } elseif ($request->frequency == 'Personalizada') {
                $validated['custom_date'] = $validated['custom_date'];
                $validated['execution_time'] = $this->combineDateAndTime($validated['custom_date'], $validated['execution_time_custom']);
            }
    
            ScheduledTask::create($validated);
    
            return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada creada con éxito');
    
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { 
                return redirect()->back()->withErrors(['task_name' => 'El nombre de la tarea ya está en uso.'])->withInput();
            }
            return redirect()->back()->with('error', 'Hubo un problema al crear la tarea programada. Por favor, inténtalo de nuevo.');
        }
    }
    
    protected function combineDateAndTime($date, $time)
    {
        return $date . ' ' . $time;
    }
    
    public function edit($id)
    {
        try {
            $scheduledTask = ScheduledTask::findOrFail($id);
            
            $scheduledTask->execution_time = \Carbon\Carbon::parse($scheduledTask->execution_time)->format('H:i');
            $scheduledTask->custom_date = \Carbon\Carbon::parse($scheduledTask->custom_date)->format('Y-m-d');
        
            return view('scheduled_tasks.edit', compact('scheduledTask'));
    
        } catch (ModelNotFoundException $e) {
            return redirect()->route('scheduled-tasks.index')->with('error', 'La tarea programada no fue encontrada.');
        }
    }
      
    public function update(StoreScheduledTaskRequest $request, $id)
    {
        try {
            $scheduledTask = ScheduledTask::findOrFail($id);
    
            $validated = $request->validated();
    
            if ($request->frequency == 'Diaria') {
                $validated['execution_time'] = $validated['execution_time_daily'];
            } elseif ($request->frequency == 'Semanal') {
                $validated['execution_time'] = $validated['execution_time_weekly'];
            } elseif ($request->frequency == 'Mensual') {
                $validated['execution_time'] = $validated['execution_time_monthly'];
            } elseif ($request->frequency == 'Personalizada') {
                $validated['custom_date'] = $validated['custom_date'];
                $validated['execution_time'] = $this->combineDateAndTime($validated['custom_date'], $validated['execution_time_custom']);
            }
    
            $scheduledTask->update($validated);
    
            return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada actualizada con éxito');
    
        } catch (\Exception $e) {
            Log::error('Error al actualizar la tarea programada: ' . $e->getMessage());
    
            return redirect()->back()->with('error', 'Hubo un problema al actualizar la tarea programada. Por favor, inténtalo de nuevo.');
        }
    }

    public function destroy($id)
    {
        try {
            $scheduledTask = ScheduledTask::findOrFail($id);
            $scheduledTask->delete();
            return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada eliminada exitosamente.');
    
        } catch (ModelNotFoundException $e) {
            return redirect()->route('scheduled-tasks.index')->with('error', 'La tarea programada no fue encontrada.');
        }
    }
}
