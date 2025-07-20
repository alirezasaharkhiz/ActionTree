<?php

namespace App\Listeners;

use App\Events\WorkflowTaskFailed;
use App\Services\WorkflowOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleWorkflowTaskFailure implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected WorkflowOrchestrator $orchestrator;

    public function __construct(WorkflowOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    public function handle(WorkflowTaskFailed $event): void
    {
        Log::error("Listener received TaskFailedEvent for task ID: {$event->workflowTask->id}. Error: " . $event->exception->getMessage());
        $this->orchestrator->processTaskFailure($event->workflowTask, $event->exception);
    }
}
