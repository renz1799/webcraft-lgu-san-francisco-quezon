<?php

namespace App\Core\Http\Requests\AccountablePersons\Concerns;

use App\Core\Support\AdminContextAuthorizer;

trait AuthorizesAccountablePersons
{
    protected function canViewAccountablePersons(): bool
    {
        return $this->userHasAnyAccountablePersonAbility([
            'accountable_persons.view',
        ]);
    }

    protected function canModifyAccountablePersons(): bool
    {
        return $this->userHasAnyAccountablePersonAbility([
            'accountable_persons.create',
            'accountable_persons.update',
            'accountable_persons.archive',
            'accountable_persons.restore',
        ]);
    }

    /**
     * @param  array<int, string>  $abilities
     */
    private function userHasAnyAccountablePersonAbility(array $abilities): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsAnyPermission($user, $abilities);
    }
}
