<?php

namespace App\Listeners;

use App\Events\WorkflowTaskCompleted;
use App\Services\WorkflowOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleWorkflowTaskCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    protected WorkflowOrchestrator $orchestrator;

    public function __construct(WorkflowOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    public function handle(WorkflowTaskCompleted $event): void
    {
        Log::info("Listener received TaskCompletedEvent for task ID: {$event->workflowTask->id}");
        $this->orchestrator->processTaskCompletion($event->workflowTask);
    }

    public function failed(WorkflowTaskCompleted $event, \Throwable $exception): void
    {
        Log::error("Task completion listener failed for task ID: {$event->workflowTask->id}. Error: " . $exception->getMessage());
    }
}
