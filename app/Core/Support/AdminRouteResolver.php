<?php

namespace App\Core\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminRouteResolver
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function routeName(string $baseName): string
    {
        $scopePrefix = $this->scopePrefix();

        if ($scopePrefix === null) {
            return $baseName;
        }

        $scopedName = $scopePrefix . '.' . ltrim($baseName, '.');

        return Route::has($scopedName) ? $scopedName : $baseName;
    }

    public function route(string $baseName, mixed $parameters = [], bool $absolute = true): string
    {
        return route($this->routeName($baseName), $parameters, $absolute);
    }

    public function isModuleScoped(): bool
    {
        return $this->scopePrefix() !== null;
    }

    public function scopedModuleId(): ?string
    {
        return $this->isModuleScoped() ? $this->context->moduleId() : null;
    }

    public function scopedModuleCode(): ?string
    {
        return $this->isModuleScoped() ? $this->context->moduleCode() : null;
    }

    public function scopePrefix(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $routeName = request()->route()?->getName();
        $moduleCode = Str::lower(trim((string) $this->context->moduleCode()));

        if (! is_string($routeName) || $routeName === '' || $moduleCode === '') {
            return null;
        }

        $scopePrefix = $moduleCode . '.';

        return Str::startsWith($routeName, $scopePrefix) ? $moduleCode : null;
    }
}
