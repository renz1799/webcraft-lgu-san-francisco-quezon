<?php

namespace App\Modules\GSO\Services\Contracts\Numbers;

interface ItrNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string;
}
