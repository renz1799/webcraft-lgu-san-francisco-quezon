<?php

namespace App\Modules\Tasks\Providers;

use App\Modules\Tasks\Builders\Contracts\TaskAdminStatsBuilderInterface;
use App\Modules\Tasks\Builders\Contracts\TaskDatatableRowBuilderInterface;
use App\Modules\Tasks\Builders\Contracts\TaskNotificationPayloadBuilderInterface;
use App\Modules\Tasks\Builders\Contracts\TaskReassignmentNoteBuilderInterface;
use App\Modules\Tasks\Builders\Contracts\TaskTimelineContextMetaBuilderInterface;
use App\Modules\Tasks\Builders\TaskAdminStatsBuilder;
use App\Modules\Tasks\Builders\TaskDatatableRowBuilder;
use App\Modules\Tasks\Builders\TaskNotificationPayloadBuilder;
use App\Modules\Tasks\Builders\TaskReassignmentNoteBuilder;
use App\Modules\Tasks\Builders\TaskTimelineContextMetaBuilder;
use App\Modules\Tasks\Models\Task;
use App\Modules\Tasks\Policies\TaskPolicy;
use App\Modules\Tasks\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use App\Modules\Tasks\Repositories\Eloquent\EloquentTaskEventRepository;
use App\Modules\Tasks\Repositories\Eloquent\EloquentTaskRepository;
use App\Modules\Tasks\Services\Contracts\TaskNotificationServiceInterface;
use App\Modules\Tasks\Services\Contracts\TaskReadServiceInterface;
use App\Modules\Tasks\Services\Contracts\TaskServiceInterface;
use App\Modules\Tasks\Services\Contracts\TaskShowActionProviderInterface;
use App\Modules\Tasks\Services\Contracts\TaskTimelineServiceInterface;
use App\Modules\Tasks\Services\TaskNotificationService;
use App\Modules\Tasks\Services\TaskReadService;
use App\Modules\Tasks\Services\TaskService;
use App\Modules\Tasks\Services\TaskShowActionProvider;
use App\Modules\Tasks\Services\TaskTimelineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerBuilders();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('modules/tasks/views'), 'tasks');

        Gate::policy(Task::class, TaskPolicy::class);

        View::composer('layouts.master', function ($view) {
            $user = Auth::user();
            $taskCounts = null;

            if ($user) {
                $cacheKey = 'task_counts:' . $user->id;

                $taskCounts = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($user) {
                    return app(TaskReadServiceInterface::class)->sidebarCounts($user);
                });
            }

            $view->with('taskCounts', $taskCounts);
        });
    }

    private function registerRepositories(): void
    {
        $this->bindMany([
            TaskRepositoryInterface::class => EloquentTaskRepository::class,
            TaskEventRepositoryInterface::class => EloquentTaskEventRepository::class,
        ]);
    }

    private function registerBuilders(): void
    {
        $this->bindMany([
            TaskDatatableRowBuilderInterface::class => TaskDatatableRowBuilder::class,
            TaskAdminStatsBuilderInterface::class => TaskAdminStatsBuilder::class,
            TaskReassignmentNoteBuilderInterface::class => TaskReassignmentNoteBuilder::class,
            TaskNotificationPayloadBuilderInterface::class => TaskNotificationPayloadBuilder::class,
            TaskTimelineContextMetaBuilderInterface::class => TaskTimelineContextMetaBuilder::class,
        ]);
    }

    private function registerServices(): void
    {
        $this->bindMany([
            TaskReadServiceInterface::class => TaskReadService::class,
            TaskServiceInterface::class => TaskService::class,
            TaskTimelineServiceInterface::class => TaskTimelineService::class,
            TaskNotificationServiceInterface::class => TaskNotificationService::class,
            TaskShowActionProviderInterface::class => TaskShowActionProvider::class,
        ], true);
    }

    /**
     * @param  array<class-string, class-string>  $map
     */
    private function bindMany(array $map, bool $asSingleton = false): void
    {
        foreach ($map as $abstract => $concrete) {
            if ($asSingleton) {
                $this->app->singleton($abstract, $concrete);
                continue;
            }

            $this->app->bind($abstract, $concrete);
        }
    }
}
