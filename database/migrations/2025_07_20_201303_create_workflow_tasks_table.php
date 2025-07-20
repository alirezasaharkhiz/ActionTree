<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_tasks', function (Blueprint $table) {
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_tasks');
    }
};
