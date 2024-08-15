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
        dd($validated);

        ScheduledTask::create($validated);
        return redirect()->route('scheduled-tasks.index')->with('success', 'Tarea programada creada exitosamente.');
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
