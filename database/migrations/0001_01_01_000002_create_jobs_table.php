<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('task_class'); // Fully qualified class name of the Task Job
            $table->json('payload')->nullable(); // Data needed for the task
            $table->enum('status', ['pending', 'queued', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_task_id')->constrained('workflow_tasks')->onDelete('cascade');
            $table->foreignId('depends_on_task_id')->constrained('workflow_tasks')->onDelete('cascade'); // The task that must complete before workflow_task_id starts
            $table->timestamps();

            $table->unique(['workflow_task_id', 'depends_on_task_id'], 'workflow_task_dependency_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_task_dependencies');
        Schema::dropIfExists('workflow_tasks');
        Schema::dropIfExists('workflows');
    }
};
