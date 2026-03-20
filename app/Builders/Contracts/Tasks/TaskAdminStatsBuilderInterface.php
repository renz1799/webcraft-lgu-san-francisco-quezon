<?php

namespace App\Builders\Contracts\Tasks;

interface TaskAdminStatsBuilderInterface
{
    public function build(array $rawStats): array;
}
