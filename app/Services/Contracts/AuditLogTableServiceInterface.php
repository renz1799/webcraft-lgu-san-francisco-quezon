<?php
// app/Services/Contracts/AuditLogTableServiceInterface.php
namespace App\Services\Contracts;

interface AuditLogTableServiceInterface
{
    public function tableData(array $filters, int $page, int $size): array;
}
