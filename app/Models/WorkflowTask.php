<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'task_class',
        'payload',
        'status',
        'started_at',
        'completed_at',
        'error_message'
    ];

    protected $casts = [
        'payload' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(WorkflowTaskDependencies::class, 'workflow_task_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(WorkflowTaskDependencies::class, 'depends_on_task_id');
    }

    // Helper to get actual dependencies (tasks that MUST complete before this one)
    public function actualDependencies()
    {
        return $this->dependencies()->with('dependsOnTask')->get()->pluck('dependsOnTask');
    }

    // Helper to get tasks that depend on this one
    public function actualDependents()
    {
        return $this->dependents()->with('workflowTask')->get()->pluck('workflowTask');
    }
}
