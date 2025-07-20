<?php

namespace App\Events;

use App\Models\WorkflowTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class WorkflowTaskFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WorkflowTask $workflowTask;
    public Throwable $exception;

    public function __construct(WorkflowTask $workflowTask, Throwable $exception)
    {
        $this->workflowTask = $workflowTask;
        $this->exception = $exception;
    }
}
