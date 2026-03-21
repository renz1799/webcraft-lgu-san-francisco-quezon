<?php

namespace App\Core\Builders\GoogleDrive;

use App\Core\Builders\Contracts\GoogleDrive\GoogleDriveFileMetadataBuilderInterface;

class GoogleDriveFileMetadataBuilder implements GoogleDriveFileMetadataBuilderInterface
{
    public function build(object $file, ?string $folderId = null, bool $isPublic = false, array $extra = []): array
    {
        $resolvedFolderId = $folderId;

        if ($resolvedFolderId === null || $resolvedFolderId === '') {
            $resolvedFolderId = (string) (($file->parents[0] ?? ''));
        }

        return array_merge([
            'drive_file_id' => (string) ($file->id ?? ''),
            'name' => (string) ($file->name ?? ''),
            'mime_type' => (string) ($file->mimeType ?? 'application/octet-stream'),
            'size' => (int) ($file->size ?? 0),
            'web_view_link' => $file->webViewLink ?? null,
            'web_content_link' => $file->webContentLink ?? null,
            'created_time' => $file->createdTime ?? null,
            'is_public' => $isPublic,
            'folder_id' => $resolvedFolderId,
        ], $extra);
    }
}
