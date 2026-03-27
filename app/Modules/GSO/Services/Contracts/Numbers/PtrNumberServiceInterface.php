<?php

namespace App\Modules\GSO\Services\Contracts\Numbers;

interface PtrNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string;
}
