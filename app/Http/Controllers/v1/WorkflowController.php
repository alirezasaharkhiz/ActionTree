<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImageJob;
use App\Jobs\SendNotificationJob;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;
use App\Services\WorkflowOrchestrator;
use Illuminate\Support\Facades\Log;

class WorkflowController extends Controller
{
    public function __construct(
        protected WorkflowOrchestrator        $orchestrator,
        protected WorkflowRepositoryInterface $workflowRepository
    )
    {
    }

    /**
     * an example of workflow.
     */
    public function startExampleWorkflow()
    {
        try {
            $tasksDefinition = [
                [
                    'id_placeholder' => 'task1_image_process',
                    'task_class' => ProcessImageJob::class,
                    'payload' => ['image_path' => 'uploads/image_a.jpg', 'quality' => 80],
                    'depends_on' => [],
                ],
                [
                    'id_placeholder' => 'task2_image_process',
                    'task_class' => ProcessImageJob::class,
                    'payload' => ['image_path' => 'uploads/image_b.png', 'quality' => 90, 'fail_sometimes' => true],
                    'depends_on' => [],
                ],
                [
                    'id_placeholder' => 'task3_notify_user_images_done',
                    'task_class' => SendNotificationJob::class,
                    'payload' => ['user_id' => 101, 'message' => 'Your images have been processed!'],
                    'depends_on' => ['task1_image_process', 'task2_image_process'],
                ],
                [
                    'id_placeholder' => 'task4_final_notification',
                    'task_class' => SendNotificationJob::class,
                    'payload' => ['user_id' => 102, 'message' => 'All workflow steps completed.'],
                    'depends_on' => ['task3_notify_user_images_done'],
                ],
            ];

            $workflow = $this->orchestrator->createAndStartWorkflow('User Media Processing Workflow', $tasksDefinition);

            return response()->json([
                'message' => 'Workflow started successfully!',
                'workflow_id' => $workflow->id,
                'status' => $workflow->status
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to start example workflow: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to start workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieves the status of a specific workflow.
     */
    public function getWorkflowStatus($id)
    {
        //TODO: this code should move to service layer
        $workflow = $this->workflowRepository->find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Workflow not found'], 404);
        }
        $workflow->load('tasks.dependencies', 'tasks.dependents');

        return response()->json($workflow);
    }
}
