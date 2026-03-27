<?php

namespace App\Modules\GSO\Services\Contracts\Numbers;

interface ParNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string;
}
