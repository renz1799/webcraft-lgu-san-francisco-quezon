<?php

return [
    App\Core\Providers\AppServiceProvider::class,
    App\Core\Providers\AuthServiceProvider::class,
    App\Core\Providers\ViewServiceProvider::class,
    App\Core\Providers\CoreServiceProvider::class,
    App\Modules\GSO\Providers\GsoServiceProvider::class,
    App\Core\Providers\TasksServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];
