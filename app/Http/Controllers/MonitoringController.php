<?php

namespace App\Http\Controllers;

use App\Models\ScheduledTask;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {        
        $executions = ScheduledTask::getAllLastExecutions();

        $executionStats = ScheduledTask::countLastExecutionResults();

        return view('monitoring.index', [
            'executions' => $executions,
            'executionStats' => $executionStats
        ]);
    }
}
