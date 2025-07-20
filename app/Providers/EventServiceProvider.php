<?php

namespace App\Providers;

use App\Events\WorkflowTaskCompleted;
use App\Events\WorkflowTaskFailed;
use App\Listeners\HandleWorkflowTaskCompletion;
use App\Listeners\HandleWorkflowTaskFailure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        WorkflowTaskCompleted::class => [
            HandleWorkflowTaskCompletion::class,
        ],
        WorkflowTaskFailed::class => [
            HandleWorkflowTaskFailure::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
