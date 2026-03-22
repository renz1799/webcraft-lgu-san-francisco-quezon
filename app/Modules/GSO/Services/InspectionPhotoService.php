<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\Inspection;
use App\Modules\GSO\Models\InspectionPhoto;
use App\Modules\GSO\Repositories\Contracts\InspectionPhotoRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InspectionRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InspectionPhotoServiceInterface;
use App\Modules\GSO\Support\InspectionStatuses;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class InspectionPhotoService implements InspectionPhotoServiceInterface
{
    public function __construct(
        private readonly InspectionRepositoryInterface $inspections,
        private readonly InspectionPhotoRepositoryInterface $photos,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
    ) {}

    public function listForInspection(string $inspectionId): array
    {
        $inspection = $this->inspections->findOrFail($inspectionId, true);

        return $this->buildPayload($inspection);
    }

    public function upload(string $actorUserId, string $inspectionId, array $files): array
    {
        return DB::transaction(function () use ($actorUserId, $inspectionId, $files) {
            $inspection = $this->inspections->findOrFail($inspectionId, true);
            $this->assertInspectionAllowsMutation($inspection);

            $folderId = $this->ensureDriveFolder($inspection);
            $uploadedDriveFileIds = [];
            $createdPhotos = [];

            try {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile || ! $file->isValid()) {
                        continue;
                    }

                    $uploaded = $this->driveFiles->upload(
                        file: $file,
                        name: null,
                        makePublic: false,
                        folderId: $folderId,
                    );

                    $uploadedDriveFileIds[] = (string) ($uploaded['drive_file_id'] ?? '');

                    $createdPhotos[] = $this->photos->create([
                        'inspection_id' => (string) $inspection->id,
                        'driver' => 'google',
                        'path' => (string) ($uploaded['drive_file_id'] ?? ''),
                        'original_name' => $this->normalizeNullableString($file->getClientOriginalName()),
                        'mime' => $this->normalizeNullableString($file->getMimeType())
                            ?? $this->normalizeNullableString($uploaded['mime_type'] ?? null),
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
                        // Best-effort cleanup for partially uploaded files.
                    }
                }

                throw $exception;
            }

            if ($createdPhotos === []) {
                throw ValidationException::withMessages([
                    'photos' => ['At least one valid image is required.'],
                ]);
            }

            $inspection = $this->inspections->findOrFail($inspectionId, true);

            $this->auditLogs->record(
                action: 'gso.inspection-photo.uploaded',
                subject: $inspection,
                changesOld: [],
                changesNew: [
                    'uploaded_count' => count($createdPhotos),
                    'photo_ids' => collect($createdPhotos)->map(fn (InspectionPhoto $photo) => (string) $photo->id)->values()->all(),
                    'drive_folder_id' => $inspection->drive_folder_id,
                ],
                meta: ['actor_user_id' => $actorUserId],
                message: 'Inspection photos uploaded: ' . $this->inspectionLabel($inspection),
                display: [
                    'summary' => 'Inspection photos uploaded: ' . $this->inspectionLabel($inspection),
                    'subject_label' => $this->inspectionLabel($inspection),
                    'sections' => [[
                        'title' => 'Photo Upload',
                        'items' => [
                            ['label' => 'Uploaded Count', 'before' => '0', 'after' => (string) count($createdPhotos)],
                            ['label' => 'Files', 'before' => 'None', 'after' => $this->photoNamesLabel($createdPhotos)],
                        ],
                    ]],
                ],
            );

            return $this->buildPayload($inspection);
        });
    }

    public function delete(string $actorUserId, string $inspectionId, string $photoId): array
    {
        return DB::transaction(function () use ($actorUserId, $inspectionId, $photoId) {
            $inspection = $this->inspections->findOrFail($inspectionId, true);
            $this->assertInspectionAllowsMutation($inspection);

            $photo = $this->photos->findForInspectionOrFail($inspectionId, $photoId);
            $before = $this->mapPhoto($photo);
            $driveDeleteStatus = 'skipped';
            $driveDeleteError = null;

            if ((string) $photo->driver === 'google' && trim((string) $photo->path) !== '') {
                try {
                    $this->driveFiles->deleteFile((string) $photo->path);
                    $driveDeleteStatus = 'deleted';
                } catch (Throwable $exception) {
                    $driveDeleteStatus = 'failed';
                    $driveDeleteError = Str::limit($exception->getMessage(), 250);
                }
            }

            $this->photos->delete($photo);
            $inspection = $this->inspections->findOrFail($inspectionId, true);

            $this->auditLogs->record(
                action: 'gso.inspection-photo.deleted',
                subject: $inspection,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: array_filter([
                    'actor_user_id' => $actorUserId,
                    'drive_delete_status' => $driveDeleteStatus,
                    'drive_delete_error' => $driveDeleteError,
                ], fn (mixed $value) => $value !== null),
                message: 'Inspection photo deleted: ' . $this->inspectionLabel($inspection),
                display: [
                    'summary' => 'Inspection photo deleted: ' . $this->inspectionLabel($inspection),
                    'subject_label' => $this->inspectionLabel($inspection),
                    'sections' => [[
                        'title' => 'Photo Lifecycle',
                        'items' => [
                            ['label' => 'Photo', 'before' => $this->displayValue($photo->original_name), 'after' => 'Deleted'],
                            ['label' => 'Drive Cleanup', 'before' => 'Pending', 'after' => ucfirst($driveDeleteStatus)],
                        ],
                    ]],
                ],
            );

            return $this->buildPayload($inspection);
        });
    }

    /**
     * @return array{inspection: array<string, mixed>, photos: array<int, array<string, mixed>>}
     */
    private function buildPayload(Inspection $inspection): array
    {
        $photos = $this->photos->listForInspection((string) $inspection->id)
            ->map(fn (InspectionPhoto $photo): array => $this->mapPhoto($photo))
            ->values()
            ->all();

        return [
            'inspection' => [
                'id' => (string) $inspection->id,
                'label' => $this->inspectionLabel($inspection),
                'po_number' => $this->normalizeNullableString($inspection->po_number),
                'status' => (string) ($inspection->status ?? ''),
                'status_text' => InspectionStatuses::labels()[(string) ($inspection->status ?? '')] ?? 'Unknown',
                'is_archived' => $inspection->trashed(),
                'photo_count' => count($photos),
                'drive_folder_id' => $this->normalizeNullableString($inspection->drive_folder_id),
            ],
            'photos' => $photos,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPhoto(InspectionPhoto $photo): array
    {
        $driver = trim((string) ($photo->driver ?? ''));
        $path = trim((string) ($photo->path ?? ''));

        return [
            'id' => (string) $photo->id,
            'inspection_id' => (string) $photo->inspection_id,
            'driver' => $driver,
            'path' => $path,
            'original_name' => $this->normalizeNullableString($photo->original_name),
            'mime' => $this->normalizeNullableString($photo->mime),
            'size' => $photo->size !== null ? (int) $photo->size : null,
            'size_text' => $this->formatBytes($photo->size),
            'caption' => $this->normalizeNullableString($photo->caption),
            'preview_url' => $this->resolvePreviewUrl($driver, $path),
            'previewable' => $this->isPreviewable($driver, $path, $photo->mime),
            'created_at' => $photo->created_at?->toDateTimeString(),
            'created_at_text' => $photo->created_at?->format('M d, Y h:i A') ?? '-',
        ];
    }

    private function assertInspectionAllowsMutation(Inspection $inspection): void
    {
        if ($inspection->trashed()) {
            throw ValidationException::withMessages([
                'inspection' => ['Archived inspections cannot be modified.'],
            ]);
        }
    }

    private function ensureDriveFolder(Inspection $inspection): string
    {
        $existingFolderId = trim((string) ($inspection->drive_folder_id ?? ''));

        if ($existingFolderId !== '') {
            return $existingFolderId;
        }

        $baseFolderId = trim((string) config(
            'gso.storage.inspection_photos_folder_id',
            config('services.google_drive.folder_id', '')
        ));

        if ($baseFolderId === '') {
            throw new RuntimeException('GSO inspection photo upload folder is not configured.');
        }

        $poNumber = trim((string) ($inspection->po_number ?? ''));

        if ($poNumber === '') {
            throw ValidationException::withMessages([
                'po_number' => ['PO number is required before uploading inspection photos.'],
            ]);
        }

        $folder = $this->driveFolders->ensureFolder($poNumber, $baseFolderId);
        $folderId = trim((string) ($folder['drive_folder_id'] ?? ''));

        if ($folderId === '') {
            throw new RuntimeException('Failed to resolve the Google Drive folder for inspection photos.');
        }

        $inspection->drive_folder_id = $folderId;
        $this->inspections->save($inspection);

        return $folderId;
    }

    /**
     * @param  array<int, InspectionPhoto>  $photos
     */
    private function photoNamesLabel(array $photos): string
    {
        $names = collect($photos)
            ->map(fn (InspectionPhoto $photo) => $this->displayValue($photo->original_name))
            ->filter(fn (string $name) => $name !== 'None')
            ->take(5)
            ->implode(', ');

        return $names !== '' ? $names : 'Uploaded image(s)';
    }

    private function inspectionLabel(Inspection $inspection): string
    {
        $itemName = trim((string) ($inspection->item_name ?? ''));
        $poNumber = trim((string) ($inspection->po_number ?? ''));

        if ($itemName !== '' && $poNumber !== '') {
            return "{$itemName} ({$poNumber})";
        }

        return $poNumber !== '' ? $poNumber : ($itemName !== '' ? $itemName : 'Inspection');
    }

    private function resolvePreviewUrl(string $driver, string $path): ?string
    {
        if ($driver === 'google' && $path !== '') {
            return route('drive.preview', ['fileId' => $path]);
        }

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return null;
    }

    private function isPreviewable(string $driver, string $path, mixed $mime): bool
    {
        if ($path === '') {
            return false;
        }

        $mime = trim((string) ($mime ?? ''));

        if ($mime !== '' && ! str_starts_with($mime, 'image/')) {
            return false;
        }

        return $driver === 'google' || Str::startsWith($path, ['http://', 'https://', '/']);
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

    private function normalizeNullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
