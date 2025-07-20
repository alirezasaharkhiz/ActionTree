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
        Schema::create('workflow_task_dependencies', function (Blueprint $table) {
            Schema::create('workflow_task_dependencies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_task_id')->constrained('workflow_tasks')->onDelete('cascade');
                $table->foreignId('depends_on_task_id')->constrained('workflow_tasks')->onDelete('cascade'); // The task that must complete before workflow_task_id starts
                $table->timestamps();

                $table->unique(['workflow_task_id', 'depends_on_task_id'], 'workflow_task_dependency_unique');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_task_dependencies');
    }
};
