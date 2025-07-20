<?php

namespace App\Repositories\Interfaces;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Collection;

interface WorkflowRepositoryInterface
{
    public function create(array $data): Workflow;
    public function find(int $id): ?Workflow;
    public function update(Workflow $workflow, array $data): bool;
    public function getAll(): Collection;
}
