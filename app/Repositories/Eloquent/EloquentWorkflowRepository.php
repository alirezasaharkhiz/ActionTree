<?php

namespace App\Repositories\Eloquent;

use App\Models\Workflow;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentWorkflowRepository implements WorkflowRepositoryInterface
{
    public function create(array $data): Workflow
    {
        return Workflow::create($data);
    }

    public function find(int $id): ?Workflow
    {
        return Workflow::find($id);
    }

    public function update(Workflow $workflow, array $data): bool
    {
        return $workflow->update($data);
    }

    public function getAll(): Collection
    {
        return Workflow::all();
    }
}
