<?php

namespace App\Core\Builders\Contracts\GoogleDrive;

interface GoogleDriveFolderNameSanitizerInterface
{
    public function sanitize(string $value): string;
}
