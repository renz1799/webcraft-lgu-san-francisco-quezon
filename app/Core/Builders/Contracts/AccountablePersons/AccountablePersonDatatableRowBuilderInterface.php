<?php

namespace App\Core\Builders\Contracts\AccountablePersons;

use App\Core\Models\AccountablePerson;

interface AccountablePersonDatatableRowBuilderInterface
{
    public function build(AccountablePerson $accountablePerson): array;
}
