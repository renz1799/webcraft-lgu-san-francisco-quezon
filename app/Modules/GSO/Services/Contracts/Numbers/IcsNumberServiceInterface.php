<?php

namespace App\Modules\GSO\Services\Contracts\Numbers;

interface IcsNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string;
}
