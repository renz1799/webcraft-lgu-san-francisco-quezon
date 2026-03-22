<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\AccountableOfficer;

interface AccountableOfficerDatatableRowBuilderInterface
{
    public function build(AccountableOfficer $accountableOfficer): array;
}
