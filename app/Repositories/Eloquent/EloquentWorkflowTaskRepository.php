<?php

namespace App\Repositories\Eloquent;

use App\Models\Workflow;
use App\Models\WorkflowTask;
use App\Models\WorkflowTaskDependencies;
use App\Repositories\Interfaces\WorkflowTaskRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentWorkflowTaskRepository implements WorkflowTaskRepositoryInterface
{
    public function create(Workflow $workflow, string $taskClass, array $payload = []): WorkflowTask
    {
        return $workflow->tasks()->create([
            'task_class' => $taskClass,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }

    public function find(int $id): ?WorkflowTask
    {
        return WorkflowTask::find($id);
    }

    public function update(WorkflowTask $task, array $data): bool
    {
        return $task->update($data);
    }

    public function getRootTasks(Workflow $workflow): Collection
    {
        return $workflow->tasks()->doesntHave('dependencies')->get();
    }

    public function getDependentTasks(WorkflowTask $task): Collection
    {
        return $task->dependents()->with('workflowTask')->get()->pluck('workflowTask');
    }

    public function getDependenciesForTask(WorkflowTask $task): Collection
    {
        return $task->dependencies()->with('dependsOnTask')->get()->pluck('dependsOnTask');
    }

    public function addDependency(WorkflowTask $task, WorkflowTask $dependsOnTask): void
    {
        WorkflowTaskDependencies::create([
            'workflow_task_id' => $task->id,
            'depends_on_task_id' => $dependsOnTask->id,
        ]);
    }

    public function getWorkflowTasksByStatus(Workflow $workflow, string $status): Collection
    {
        return $workflow->tasks()->where('status', $status)->get();
    }

    public function countWorkflowTasksByStatus(Workflow $workflow, string $status): int
    {
        return $workflow->tasks()->where('status', $status)->count();
    }

    public function countAllWorkflowTasks(Workflow $workflow): int
    {
        return $workflow->tasks()->count();
    }
}
