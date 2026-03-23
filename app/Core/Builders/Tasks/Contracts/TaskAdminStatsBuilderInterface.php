<?php

namespace App\Core\Builders\Tasks\Contracts;

interface TaskAdminStatsBuilderInterface
{
    public function build(array $rawStats): array;
}
