<?php

namespace App\Events;

use App\Models\WorkflowTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowTaskCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WorkflowTask $workflowTask;

    public function __construct(WorkflowTask $workflowTask)
    {
        $this->workflowTask = $workflowTask;
    }
}
