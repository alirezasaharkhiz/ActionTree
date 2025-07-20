<?php

namespace App\Providers;

use App\Repositories\Eloquent\EloquentWorkflowRepository;
use App\Repositories\Eloquent\EloquentWorkflowTaskRepository;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;
use App\Repositories\Interfaces\WorkflowTaskRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WorkflowRepositoryInterface::class, EloquentWorkflowRepository::class);
        $this->app->bind(WorkflowTaskRepositoryInterface::class, EloquentWorkflowTaskRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
