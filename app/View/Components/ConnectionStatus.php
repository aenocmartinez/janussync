<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ConnectionStatus extends Component
{
    public $title;
    public $connectionStatus;
    public $id;

    public function __construct($title, $connectionStatus, $id)
    {
        $this->title = $title;
        $this->connectionStatus = $connectionStatus;
        $this->id = $id;
    }

    public function render()
    {
        return view('components.connection-status');
    }
}
