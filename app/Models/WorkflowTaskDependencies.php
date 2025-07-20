<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTaskDependencies  extends Model
{
    use HasFactory;

    protected $fillable = ['workflow_task_id', 'depends_on_task_id'];

    public function workflowTask(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'workflow_task_id');
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'depends_on_task_id');
    }
}
