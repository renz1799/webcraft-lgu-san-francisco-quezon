<?php

namespace App\Modules\GSO\Services\Contracts\PTR;

interface PtrNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string;
}
