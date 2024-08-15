<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTask;
use Illuminate\Http\Request;

class ScheduledTaskController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        $scheduledTasks = ScheduledTask::query()
            ->when($search, function ($query, $search) {
                return $query->where(function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('frequency', 'like', '%' . $search . '%')
                          ->orWhere('status', '=', $search); // Cambiar LIKE a =
                });
            })
            ->orderBy('name')  
            ->orderBy('status')
            ->paginate(8);
    
        return view('scheduled_tasks.index', [
            'scheduledTasks' => $scheduledTasks,
        ]);
    }
    

    public function create() {
        return view('scheduled_tasks.index');
    }

    public function edit($id) {
        return view('scheduled_tasks.index');
    }

    public function destroy($id) {
        return view('scheduled_tasks.index');
    }
}
