<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTask;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {        
        $executions = ScheduledTask::getAllLastExecutions();
        return view('monitoring.index', [
            'executions' => $executions
        ]);
    }
}
