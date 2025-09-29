<?php
// app/Services/Contracts/LoginLogServiceInterface.php

namespace App\Services\Contracts;

interface LoginLogServiceInterface
{
    /**
     * Returns an array: ['recordsTotal'=>int, 'recordsFiltered'=>int, 'data'=>array]
     */
    public function datatable(array $params): array;
}
