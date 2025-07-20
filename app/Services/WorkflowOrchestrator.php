<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowTask;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;
use App\Repositories\Interfaces\WorkflowTaskRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorkflowOrchestrator
{
    public function __construct(
        protected WorkflowRepositoryInterface $workflowRepository,
        protected WorkflowTaskRepositoryInterface $workflowTaskRepository
    ) {
    }

    /**
     * Creates and starts a new workflow with defined tasks and dependencies.
     * This method encapsulates the workflow definition and initial dispatch logic.
     * @param string $workflowName
     * @param array $tasksData An array of [id_placeholder, task_class, payload, depends_on_ids[]]
     * @return Workflow
     * @throws \Exception
     */
    public function createAndStartWorkflow(string $workflowName, array $tasksData): Workflow
    {
        return DB::transaction(function () use ($workflowName, $tasksData) {
            $workflow = $this->workflowRepository->create(['name' => $workflowName]);

            $createdTasks = [];
            foreach ($tasksData as $data) {
                $task = $this->workflowTaskRepository->create($workflow, $data['task_class'], $data['payload'] ?? []);
                $createdTasks[$data['id_placeholder']] = $task; // Store with placeholder for dependency mapping
            }

            // Establish dependencies
            foreach ($tasksData as $data) {
                if (!empty($data['depends_on'])) {
                    $task = $createdTasks[$data['id_placeholder']];
                    foreach ($data['depends_on'] as $depPlaceholderId) {
                        $dependsOnTask = $createdTasks[$depPlaceholderId];
                        $this->workflowTaskRepository->addDependency($task, $dependsOnTask);
                    }
                }
            }

            $this->startWorkflow($workflow); // Delegate to the internal start method

            return $workflow;
        });
    }

    /**
     * Internal method to start a pre-existing workflow.
     * @param Workflow $workflow
     */
    protected function startWorkflow(Workflow $workflow): void
    {
        $this->workflowRepository->update($workflow, ['status' => 'running', 'started_at' => now()]);
        Log::info("Workflow '{$workflow->name}' (ID: {$workflow->id}) started.");

        // Find and dispatch root tasks (tasks with no dependencies)
        $rootTasks = $this->workflowTaskRepository->getRootTasks($workflow);

        foreach ($rootTasks as $task) {
            if ($task->status === 'pending') {
                $this->dispatchTask($task);
            }
        }

        // If no tasks found at all, mark workflow as completed immediately
        if ($this->workflowTaskRepository->countAllWorkflowTasks($workflow) === 0) {
            $this->workflowRepository->update($workflow, ['status' => 'completed', 'completed_at' => now()]);
            Log::info("Workflow '{$workflow->name}' (ID: {$workflow->id}) completed instantly as it had no tasks.");
        }
    }

    /**
     * Processes a task completion event.
     * @param WorkflowTask $completedTask The task that just completed.
     */
    public function processTaskCompletion(WorkflowTask $completedTask): void
    {
        DB::transaction(function () use ($completedTask) {
            // Refresh the task to get the latest status, preventing race conditions
            $completedTask = $this->workflowTaskRepository->find($completedTask->id);
            if (!$completedTask || $completedTask->status !== 'completed') {
                Log::warning("Task ID: {$completedTask->id} completion event received, but status is not 'completed'. Current status: " . ($completedTask ? $completedTask->status : 'not found'));
                return;
            }

            Log::info("Processing completion for task ID: {$completedTask->id}. Status: {$completedTask->status}");

            $workflow = $this->workflowRepository->find($completedTask->workflow_id); // Fetch workflow via repository
            if (!$workflow) {
                Log::error("Workflow not found for completed task ID: {$completedTask->id}");
                return;
            }

            $totalTasks = $this->workflowTaskRepository->countAllWorkflowTasks($workflow);
            $completedTasksCount = $this->workflowTaskRepository->countWorkflowTasksByStatus($workflow, 'completed');
            $failedTasksCount = $this->workflowTaskRepository->countWorkflowTasksByStatus($workflow, 'failed');
            $cancelledTasksCount = $this->workflowTaskRepository->countWorkflowTasksByStatus($workflow, 'cancelled');


            // If there are any failed or cancelled tasks, mark the workflow as failed/cancelled
            if ($failedTasksCount > 0) {
                $this->workflowRepository->update($workflow, ['status' => 'failed', 'completed_at' => now()]);
                Log::error("Workflow '{$workflow->name}' (ID: {$workflow->id}) marked as FAILED due to a task failure.");
                return;
            }
            if ($cancelledTasksCount > 0) {
                $this->workflowRepository->update($workflow, ['status' => 'cancelled', 'completed_at' => now()]);
                Log::warning("Workflow '{$workflow->name}' (ID: {$workflow->id}) marked as CANCELLED.");
                return;
            }

            // If all tasks are completed, mark the workflow as completed
            if ($totalTasks > 0 && ($completedTasksCount + $failedTasksCount + $cancelledTasksCount) === $totalTasks) {
                $this->workflowRepository->update($workflow, ['status' => 'completed', 'completed_at' => now()]);
                Log::info("Workflow '{$workflow->name}' (ID: {$workflow->id}) completed successfully.");
                return;
            }

            // Find tasks that depend on the just completed task
            $dependentTasks = $this->workflowTaskRepository->getDependentTasks($completedTask);
            Log::info("Task ID: {$completedTask->id} completed. Found " . $dependentTasks->count() . " dependent tasks.");

            foreach ($dependentTasks as $dependentTask) {
                // Ensure the dependent task is still pending before checking its dependencies
                if ($dependentTask->status === 'pending') {
                    $allDependenciesMet = true;
                    $dependentTaskDependencies = $this->workflowTaskRepository->getDependenciesForTask($dependentTask);

                    foreach ($dependentTaskDependencies as $dependency) {
                        if ($dependency->status !== 'completed') {
                            $allDependenciesMet = false;
                            break;
                        }
                    }

                    if ($allDependenciesMet) {
                        $this->dispatchTask($dependentTask);
                    }
                }
            }
        });
    }

    /**
     * Processes a task failure event.
     * @param WorkflowTask $failedTask The task that just failed.
     * @param Throwable $exception The exception that caused the failure.
     */
    public function processTaskFailure(WorkflowTask $failedTask, Throwable $exception): void
    {
        DB::transaction(function () use ($failedTask, $exception) {
            // Refresh the task to get the latest status
            $failedTask = $this->workflowTaskRepository->find($failedTask->id);
            if ($failedTask) {
                $this->workflowTaskRepository->update($failedTask, [
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);
            }
            Log::error("Task ID: {$failedTask->id} failed. Error: " . $exception->getMessage());

            $workflow = $this->workflowRepository->find($failedTask->workflow_id);
            if ($workflow) {
                $this->workflowRepository->update($workflow, ['status' => 'failed', 'completed_at' => now()]); // Mark workflow as failed
                Log::error("Workflow '{$workflow->name}' (ID: {$workflow->id}) marked as FAILED due to task ID: {$failedTask->id} failure.");
            }
            //TODO: Cancel all remaining pending tasks in this workflow
            //TODO: Notify administrators
        });
    }

    /**
     * Dispatches a WorkflowTask to the Laravel queue.
     * @param WorkflowTask $task The WorkflowTask record to dispatch.
     */
    protected function dispatchTask(WorkflowTask $task): void
    {
        try {
            if (!class_exists($task->task_class)) { // No need to check for BaseWorkflowTaskJob or TaskInterface
                throw new \InvalidArgumentException("Task class {$task->task_class} does not exist.");
            }

            dispatch(new $task->task_class($task, $task->payload ?? []));

            $this->workflowTaskRepository->update($task, ['status' => 'queued']);
            Log::info("Task '{$task->task_class}' (ID: {$task->id}) dispatched to queue.");
        } catch (Throwable $e) {
            $this->workflowTaskRepository->update($task, [
                'status' => 'failed',
                'error_message' => "Dispatch failed: " . $e->getMessage(),
            ]);
            Log::error("Failed to dispatch task ID: {$task->id}. Error: " . $e->getMessage());

            $this->processTaskFailure($task, $e);
        }
    }
}
