<?php

namespace App\Core\Builders\Contracts\AuditLogs;

interface AuditLogMetaBuilderInterface
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $display
     * @return array<string, mixed>|null
     */
    public function build(array $meta = [], array $display = []): ?array;
}
