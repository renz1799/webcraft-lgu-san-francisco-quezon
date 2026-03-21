<?php

namespace App\Core\Builders\GoogleDrive;

use App\Core\Builders\Contracts\GoogleDrive\GoogleDriveFolderNameSanitizerInterface;
use Illuminate\Support\Str;

class GoogleDriveFolderNameSanitizer implements GoogleDriveFolderNameSanitizerInterface
{
    public function sanitize(string $value): string
    {
        $normalized = Str::ascii($value);
        $normalized = preg_replace('/[^A-Za-z0-9 ._()-]+/', '-', $normalized) ?? '';
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';
        $normalized = preg_replace('/-+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, " .-\t\n\r\0\x0B");

        return $normalized !== '' ? $normalized : 'folder';
    }
}
