<?php

namespace App\Modules\GSO\Services\Contracts\Numbers;

interface WmrNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string;
}
