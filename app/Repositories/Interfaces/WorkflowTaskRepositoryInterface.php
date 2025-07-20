<?php

namespace App\Repositories\Interfaces;

use App\Models\WorkflowTask;
use App\Models\Workflow;
use Illuminate\Support\Collection;

interface WorkflowTaskRepositoryInterface
{
    public function create(Workflow $workflow, string $taskClass, array $payload = []): WorkflowTask;
    public function find(int $id): ?WorkflowTask;
    public function update(WorkflowTask $task, array $data): bool;
    public function getRootTasks(Workflow $workflow): Collection;
    public function getDependentTasks(WorkflowTask $task): Collection;
    public function getDependenciesForTask(WorkflowTask $task): Collection;
    public function addDependency(WorkflowTask $task, WorkflowTask $dependsOnTask): void;
    public function getWorkflowTasksByStatus(Workflow $workflow, string $status): Collection;
    public function countWorkflowTasksByStatus(Workflow $workflow, string $status): int;
    public function countAllWorkflowTasks(Workflow $workflow): int;
}
