<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Core\Models\Department;

interface DepartmentDatatableRowBuilderInterface
{
    public function build(Department $department): array;
}
