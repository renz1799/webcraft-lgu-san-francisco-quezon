<?php

namespace App\Core\Http\Requests\AccountablePersons\Concerns;

trait AuthorizesAccountablePersons
{
    protected function canViewAccountablePersons(): bool
    {
        return $this->userHasAnyAccountablePersonAbility([
            'view Accountable Persons',
            'view Accountable Officers',
        ]);
    }

    protected function canModifyAccountablePersons(): bool
    {
        return $this->userHasAnyAccountablePersonAbility([
            'modify Accountable Persons',
            'modify Accountable Officers',
        ]);
    }

    /**
     * @param  array<int, string>  $abilities
     */
    private function userHasAnyAccountablePersonAbility(array $abilities): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['Administrator', 'admin'])) {
            return true;
        }

        foreach ($abilities as $ability) {
            if ($user->can($ability)) {
                return true;
            }
        }

        return false;
    }
}
