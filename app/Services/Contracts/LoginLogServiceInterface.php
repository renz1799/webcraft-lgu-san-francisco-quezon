<?php
// app/Services/Contracts/LoginLogServiceInterface.php

namespace App\Services\Contracts;

interface LoginLogServiceInterface
{
    /**
     * Returns datatable payload: ['data'=>array, 'last_page'=>int, 'total'=>int].
     */
    public function datatable(array $params): array;
}
