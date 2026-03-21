<?php

namespace App\Modules\Tasks\Builders\Contracts;

interface TaskAdminStatsBuilderInterface
{
    public function build(array $rawStats): array;
}
