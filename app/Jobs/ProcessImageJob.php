<?php

namespace App\Jobs;

use App\Events\WorkflowTaskCompleted;
use App\Events\WorkflowTaskFailed;
use App\Models\WorkflowTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public WorkflowTask $workflowTask;
    public array $payload;

    public $tries = 3;

    public function __construct(WorkflowTask $workflowTask, array $payload = [])
    {
        $this->workflowTask = $workflowTask;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->workflowTask->refresh();

        if (in_array($this->workflowTask->status, ['completed', 'failed', 'cancelled', 'running'])) {
            return;
        }

        $this->workflowTask->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            Log::info("Processing image for task ID: {$this->workflowTask->id}", $this->payload);
            sleep(rand(1, 3)); // Simulate work
            if (isset($this->payload['fail_sometimes']) && rand(0, 1) === 0) {
                throw new \Exception("Simulated image processing failure for task " . $this->workflowTask->id);
            }
            Log::info("Image processed successfully for task ID: {$this->workflowTask->id}");
            // actual logic goes here

            $this->workflowTask->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            event(new WorkflowTaskCompleted($this->workflowTask));
        } catch (Throwable $e) {
            $this->workflowTask->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            event(new WorkflowTaskFailed($this->workflowTask, $e));
            throw $e;
        }
    }
}
