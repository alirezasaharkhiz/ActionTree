<?php

use App\Http\Controllers\v1\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/start-workflow', [WorkflowController::class, 'startExampleWorkflow']);
Route::get('/workflow-status/{id}', [WorkflowController::class, 'getWorkflowStatus']);
