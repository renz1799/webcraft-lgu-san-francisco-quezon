<?php

namespace App\Builders\Contracts\GoogleDrive;

interface GoogleDriveFileMetadataBuilderInterface
{
    public function build(object $file, ?string $folderId = null, bool $isPublic = false, array $extra = []): array;
}
