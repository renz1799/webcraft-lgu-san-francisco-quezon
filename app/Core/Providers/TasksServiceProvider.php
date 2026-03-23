<?php

namespace App\Core\Providers;

use App\Core\Builders\Tasks\Contracts\TaskAdminStatsBuilderInterface;
use App\Core\Builders\Tasks\Contracts\TaskDatatableRowBuilderInterface;
use App\Core\Builders\Tasks\Contracts\TaskNotificationPayloadBuilderInterface;
use App\Core\Builders\Tasks\Contracts\TaskReassignmentNoteBuilderInterface;
use App\Core\Builders\Tasks\Contracts\TaskTimelineContextMetaBuilderInterface;
use App\Core\Builders\Tasks\Contracts\UserTaskReassignOptionBuilderInterface;
use App\Core\Builders\Tasks\TaskAdminStatsBuilder;
use App\Core\Builders\Tasks\TaskDatatableRowBuilder;
use App\Core\Builders\Tasks\TaskNotificationPayloadBuilder;
use App\Core\Builders\Tasks\TaskReassignmentNoteBuilder;
use App\Core\Builders\Tasks\TaskTimelineContextMetaBuilder;
use App\Core\Builders\Tasks\UserTaskReassignOptionBuilder;
use App\Core\Models\Tasks\Task;
use App\Core\Policies\Tasks\TaskPolicy;
use App\Core\Repositories\Tasks\Contracts\TaskEventRepositoryInterface;
use App\Core\Repositories\Tasks\Contracts\TaskRepositoryInterface;
use App\Core\Repositories\Tasks\Eloquent\EloquentTaskEventRepository;
use App\Core\Repositories\Tasks\Eloquent\EloquentTaskRepository;
use App\Core\Services\Tasks\Contracts\TaskNotificationServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskReadServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskShowActionProviderInterface;
use App\Core\Services\Tasks\Contracts\TaskTimelineServiceInterface;
use App\Core\Services\Tasks\TaskNotificationService;
use App\Core\Services\Tasks\TaskReadService;
use App\Core\Services\Tasks\TaskService;
use App\Core\Services\Tasks\TaskShowActionProvider;
use App\Core\Services\Tasks\TaskTimelineService;
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
        $this->loadViewsFrom(resource_path('core/views/tasks'), 'tasks');

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
            UserTaskReassignOptionBuilderInterface::class => UserTaskReassignOptionBuilder::class,
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
