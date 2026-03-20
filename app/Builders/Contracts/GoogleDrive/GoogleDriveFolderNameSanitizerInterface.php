<?php

namespace App\Builders\Contracts\GoogleDrive;

interface GoogleDriveFolderNameSanitizerInterface
{
    public function sanitize(string $value): string;
}
