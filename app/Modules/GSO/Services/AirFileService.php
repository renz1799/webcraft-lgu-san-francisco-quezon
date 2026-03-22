<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirFile;
use App\Modules\GSO\Repositories\Contracts\AirFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AirFileServiceInterface;
use App\Modules\GSO\Support\AirStatuses;
use App\Modules\GSO\Support\InventoryFileTypes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AirFileService implements AirFileServiceInterface
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirFileRepositoryInterface $files,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
    ) {}

    public function listForAir(string $airId): array
    {
        $air = $this->airs->findOrFail($airId, true);

        return $this->buildPayload($air);
    }

    public function upload(string $actorUserId, string $airId, array $files, ?string $type = null): array
    {
        return DB::transaction(function () use ($actorUserId, $airId, $files, $type): array {
            $air = $this->airs->findOrFail($airId, true);
            $this->assertAirAllowsMutation($air);

            $folderId = $this->ensureDriveFolder($air);
            $uploadedDriveFileIds = [];
            $createdFiles = [];
            $hasPrimary = $this->files->listForAir((string) $air->id)
                ->contains(fn (AirFile $file): bool => (bool) $file->is_primary);

            try {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile || ! $file->isValid()) {
                        continue;
                    }

                    $resolvedType = $this->resolveUploadType($file, $type);
                    $uploaded = $this->driveFiles->upload(
                        file: $file,
                        name: null,
                        makePublic: false,
                        folderId: $folderId,
                    );

                    $uploadedDriveFileIds[] = (string) ($uploaded['drive_file_id'] ?? '');

                    $createdFiles[] = $this->files->create([
                        'air_id' => (string) $air->id,
                        'driver' => 'google',
                        'path' => (string) ($uploaded['drive_file_id'] ?? ''),
                        'type' => $resolvedType,
                        'is_primary' => ! $hasPrimary && count($createdFiles) === 0,
                        'position' => $this->files->nextPositionForAir((string) $air->id),
                        'original_name' => $this->nullableString($file->getClientOriginalName()),
                        'mime' => $this->nullableString($file->getMimeType())
                            ?? $this->nullableString($uploaded['mime_type'] ?? null),
                        'size' => $file->getSize(),
                        'caption' => null,
                    ]);
                }
            } catch (Throwable $exception) {
                foreach ($uploadedDriveFileIds as $driveFileId) {
                    if (trim($driveFileId) === '') {
                        continue;
                    }

                    try {
                        $this->driveFiles->deleteFile($driveFileId);
                    } catch (Throwable) {
                        // Best-effort cleanup.
                    }
                }

                throw $exception;
            }

            if ($createdFiles === []) {
                throw ValidationException::withMessages([
                    'files' => ['At least one valid image or PDF is required.'],
                ]);
            }

            $air = $this->airs->findOrFail($airId, true);

            $this->auditLogs->record(
                action: 'gso.air.file.uploaded',
                subject: $air,
                changesOld: [],
                changesNew: [
                    'uploaded_count' => count($createdFiles),
                    'file_ids' => collect($createdFiles)->map(fn (AirFile $createdFile) => (string) $createdFile->id)->values()->all(),
                    'drive_folder_id' => $air->drive_folder_id,
                ],
                meta: ['actor_user_id' => $actorUserId],
                message: 'AIR files uploaded: ' . $this->airLabel($air),
                display: [
                    'summary' => 'AIR files uploaded: ' . $this->airLabel($air),
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Document Upload',
                        'items' => [
                            ['label' => 'Uploaded Count', 'before' => '0', 'after' => (string) count($createdFiles)],
                            ['label' => 'Files', 'before' => 'None', 'after' => $this->fileNamesLabel($createdFiles)],
                        ],
                    ]],
                ],
            );

            return $this->buildPayload($air);
        });
    }

    public function preview(string $airId, string $fileId): array
    {
        $air = $this->airs->findOrFail($airId, true);
        $file = $this->files->findForAirOrFail((string) $air->id, $fileId);

        if ((string) $file->driver === 'google') {
            $downloaded = $this->driveFiles->download((string) $file->path);

            return [
                'name' => (string) ($downloaded['name'] ?? ($file->original_name ?? 'file')),
                'mime' => (string) ($downloaded['mime_type'] ?? ($file->mime ?? 'application/octet-stream')),
                'bytes' => (string) ($downloaded['bytes'] ?? ''),
            ];
        }

        $disk = Storage::disk('public');
        $path = trim((string) ($file->path ?? ''));

        if ($path === '' || ! $disk->exists($path)) {
            throw new RuntimeException('File not found.');
        }

        $absolutePath = $disk->path($path);

        return [
            'name' => (string) ($file->original_name ?? basename($absolutePath)),
            'mime' => (string) ($file->mime ?? 'application/octet-stream'),
            'bytes' => file_get_contents($absolutePath) ?: '',
        ];
    }

    public function delete(string $actorUserId, string $airId, string $fileId): array
    {
        return DB::transaction(function () use ($actorUserId, $airId, $fileId): array {
            $air = $this->airs->findOrFail($airId, true);
            $this->assertAirAllowsMutation($air);

            $file = $this->files->findForAirOrFail((string) $air->id, $fileId);
            $before = $this->mapFile($file, $air);

            $this->deleteStoredFile($file);
            $this->files->delete($file);

            $replacementPrimary = $this->files->listForAir((string) $air->id)->first();
            if ($file->is_primary && $replacementPrimary instanceof AirFile) {
                $replacementPrimary->is_primary = true;
                $this->files->save($replacementPrimary);
            }

            $this->auditLogs->record(
                action: 'gso.air.file.deleted',
                subject: $air,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'AIR file deleted: ' . $this->airLabel($air),
                display: [
                    'summary' => 'AIR file deleted: ' . $this->airLabel($air),
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Document Lifecycle',
                        'items' => [
                            ['label' => 'AIR', 'before' => $this->airLabel($air), 'after' => $this->airLabel($air)],
                            ['label' => 'File', 'before' => $this->displayValue($file->original_name), 'after' => 'Deleted'],
                        ],
                    ]],
                ],
            );

            $air = $this->airs->findOrFail($airId, true);

            return $this->buildPayload($air);
        });
    }

    public function setPrimary(string $actorUserId, string $airId, string $fileId): array
    {
        return DB::transaction(function () use ($actorUserId, $airId, $fileId): array {
            $air = $this->airs->findOrFail($airId, true);
            $this->assertAirAllowsMutation($air);

            $file = $this->files->findForAirOrFail((string) $air->id, $fileId);
            $before = $this->mapFile($file, $air);

            $this->files->clearPrimaryForAir((string) $air->id, (string) $file->id);
            $file->is_primary = true;
            $this->files->save($file);

            $this->auditLogs->record(
                action: 'gso.air.file.primary-set',
                subject: $air,
                changesOld: $before,
                changesNew: ['is_primary' => true],
                meta: ['actor_user_id' => $actorUserId],
                message: 'AIR file marked as primary: ' . $this->airLabel($air),
                display: [
                    'summary' => 'AIR file marked as primary: ' . $this->airLabel($air),
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Primary Document',
                        'items' => [
                            ['label' => 'AIR', 'before' => $this->airLabel($air), 'after' => $this->airLabel($air)],
                            ['label' => 'File', 'before' => $this->displayValue($file->original_name), 'after' => $this->displayValue($file->original_name)],
                            ['label' => 'Primary', 'before' => 'No', 'after' => 'Yes'],
                        ],
                    ]],
                ],
            );

            $air = $this->airs->findOrFail($airId, true);

            return $this->buildPayload($air);
        });
    }

    /**
     * @return array{air: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    private function buildPayload(Air $air): array
    {
        $files = $this->files->listForAir((string) $air->id)
            ->map(fn (AirFile $file): array => $this->mapFile($file, $air))
            ->values()
            ->all();

        return [
            'air' => [
                'id' => (string) $air->id,
                'label' => $this->airLabel($air),
                'status' => (string) ($air->status ?? ''),
                'status_text' => AirStatuses::label((string) ($air->status ?? '')),
                'po_number' => $this->nullableString($air->po_number),
                'is_archived' => $air->trashed(),
                'drive_folder_id' => $this->nullableString($air->drive_folder_id),
                'file_count' => count($files),
            ],
            'files' => $files,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapFile(AirFile $file, Air $air): array
    {
        return [
            'id' => (string) $file->id,
            'air_id' => (string) $file->air_id,
            'driver' => (string) ($file->driver ?? ''),
            'path' => (string) ($file->path ?? ''),
            'type' => (string) ($file->type ?? ''),
            'type_text' => InventoryFileTypes::labels()[(string) ($file->type ?? '')] ?? 'File',
            'is_primary' => (bool) $file->is_primary,
            'position' => (int) ($file->position ?? 0),
            'original_name' => $this->nullableString($file->original_name),
            'mime' => $this->nullableString($file->mime),
            'size' => $file->size !== null ? (int) $file->size : null,
            'size_text' => $this->formatBytes($file->size),
            'caption' => $this->nullableString($file->caption),
            'preview_url' => route('gso.air.files.preview', [
                'air' => $air->id,
                'file' => $file->id,
            ]),
            'is_image' => str_starts_with((string) ($file->mime ?? ''), 'image/'),
            'created_at' => $file->created_at?->toDateTimeString(),
            'created_at_text' => $file->created_at?->format('M d, Y h:i A') ?? '-',
        ];
    }

    private function assertAirAllowsMutation(Air $air): void
    {
        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Archived AIR records cannot manage document files.'],
            ]);
        }
    }

    private function ensureDriveFolder(Air $air): string
    {
        $existingFolderId = trim((string) ($air->drive_folder_id ?? ''));

        if ($existingFolderId !== '') {
            return $existingFolderId;
        }

        $baseFolderId = trim((string) config(
            'gso.storage.air_files_folder_id',
            config('services.google_drive.folder_id', '')
        ));

        if ($baseFolderId === '') {
            throw new RuntimeException('GSO AIR file folder is not configured.');
        }

        $poNumber = trim((string) ($air->po_number ?? ''));

        if ($poNumber === '') {
            throw ValidationException::withMessages([
                'po_number' => ['PO number is required before uploading AIR files.'],
            ]);
        }

        $folder = $this->driveFolders->ensureFolder($poNumber, $baseFolderId);
        $folderId = trim((string) ($folder['drive_folder_id'] ?? ''));

        if ($folderId === '') {
            throw new RuntimeException('Failed to resolve the Google Drive folder for AIR files.');
        }

        $air->drive_folder_id = $folderId;
        $this->airs->save($air);

        return $folderId;
    }

    private function resolveUploadType(UploadedFile $file, ?string $type): string
    {
        $type = trim((string) ($type ?? ''));

        if ($type !== '') {
            if (! in_array($type, InventoryFileTypes::values(), true)) {
                throw ValidationException::withMessages([
                    'type' => ['File type is invalid.'],
                ]);
            }

            return $type;
        }

        $mime = (string) ($file->getMimeType() ?? '');

        if (str_starts_with($mime, 'image/')) {
            return InventoryFileTypes::PHOTO;
        }

        if ($mime === 'application/pdf') {
            return InventoryFileTypes::PDF;
        }

        throw ValidationException::withMessages([
            'files' => ['Only images and PDFs are supported right now.'],
        ]);
    }

    private function deleteStoredFile(AirFile $file): void
    {
        $driver = trim((string) ($file->driver ?? ''));
        $path = trim((string) ($file->path ?? ''));

        if ($driver === 'google') {
            if ($path !== '') {
                $this->driveFiles->deleteFile($path);
            }

            return;
        }

        if ($path !== '') {
            try {
                Storage::disk('public')->delete($path);
            } catch (Throwable) {
                // Best-effort cleanup.
            }
        }
    }

    /**
     * @param  array<int, AirFile>  $files
     */
    private function fileNamesLabel(array $files): string
    {
        $names = collect($files)
            ->map(fn (AirFile $file) => $this->displayValue($file->original_name))
            ->filter(fn (string $name) => $name !== 'None')
            ->take(5)
            ->implode(', ');

        return $names !== '' ? $names : 'Uploaded file(s)';
    }

    private function airLabel(Air $air): string
    {
        $poNumber = trim((string) ($air->po_number ?? ''));
        $airNumber = trim((string) ($air->air_number ?? ''));

        if ($poNumber !== '' && $airNumber !== '') {
            return "{$poNumber} / {$airNumber}";
        }

        return $poNumber !== '' ? $poNumber : ($airNumber !== '' ? $airNumber : 'AIR Record');
    }

    private function formatBytes(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $bytes = (int) $value;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $normalized = $bytes / (1024 ** $power);

        return number_format($normalized, $power === 0 ? 0 : 1) . ' ' . $units[$power];
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
