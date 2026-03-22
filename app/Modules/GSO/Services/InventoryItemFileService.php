<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\InspectionPhoto;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemFile;
use App\Modules\GSO\Repositories\Contracts\InspectionRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemFileServiceInterface;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryFileTypes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class InventoryItemFileService implements InventoryItemFileServiceInterface
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly InventoryItemFileRepositoryInterface $files,
        private readonly InspectionRepositoryInterface $inspections,
        private readonly InventoryItemEventServiceInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
    ) {}

    public function listForInventoryItem(string $inventoryItemId): array
    {
        $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);

        return $this->buildPayload($inventoryItem);
    }

    public function upload(string $actorUserId, string $inventoryItemId, array $files, ?string $type = null): array
    {
        return DB::transaction(function () use ($actorUserId, $inventoryItemId, $files, $type) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);
            $this->assertInventoryItemAllowsMutation($inventoryItem);

            $folderId = $this->ensureDriveFolder($inventoryItem);
            $uploadedDriveFileIds = [];
            $createdFiles = [];

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
                        'inventory_item_id' => (string) $inventoryItem->id,
                        'driver' => 'google',
                        'path' => (string) ($uploaded['drive_file_id'] ?? ''),
                        'type' => $resolvedType,
                        'is_primary' => false,
                        'position' => $this->files->nextPositionForInventoryItem((string) $inventoryItem->id),
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
                        // Best-effort cleanup on partial uploads.
                    }
                }

                throw $exception;
            }

            if ($createdFiles === []) {
                throw ValidationException::withMessages([
                    'files' => ['At least one valid image or PDF is required.'],
                ]);
            }

            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);

            $this->auditLogs->record(
                action: 'gso.inventory-item.file.uploaded',
                subject: $inventoryItem,
                changesOld: [],
                changesNew: [
                    'uploaded_count' => count($createdFiles),
                    'file_ids' => collect($createdFiles)->map(fn (InventoryItemFile $createdFile) => (string) $createdFile->id)->values()->all(),
                ],
                meta: ['actor_user_id' => $actorUserId],
                message: 'Inventory files uploaded: ' . $this->inventoryItemLabel($inventoryItem),
                display: [
                    'summary' => 'Inventory files uploaded: ' . $this->inventoryItemLabel($inventoryItem),
                    'subject_label' => $this->inventoryItemLabel($inventoryItem),
                    'sections' => [[
                        'title' => 'File Upload',
                        'items' => [
                            ['label' => 'Uploaded Count', 'before' => '0', 'after' => (string) count($createdFiles)],
                            ['label' => 'Files', 'before' => 'None', 'after' => $this->fileNamesLabel($createdFiles)],
                        ],
                    ]],
                ],
            );

            return $this->buildPayload($inventoryItem);
        });
    }

    public function delete(string $actorUserId, string $inventoryItemId, string $fileId): array
    {
        return DB::transaction(function () use ($actorUserId, $inventoryItemId, $fileId) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);
            $this->assertInventoryItemAllowsMutation($inventoryItem);

            $file = $this->files->findForInventoryItemOrFail($inventoryItemId, $fileId);
            $before = $this->mapFile($file);

            $this->deleteStoredFile($file);
            $this->files->delete($file);

            $this->auditLogs->record(
                action: 'gso.inventory-item.file.deleted',
                subject: $inventoryItem,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'Inventory file deleted: ' . $this->inventoryItemLabel($inventoryItem),
                display: [
                    'summary' => 'Inventory file deleted: ' . $this->inventoryItemLabel($inventoryItem),
                    'subject_label' => $this->inventoryItemLabel($inventoryItem),
                    'sections' => [[
                        'title' => 'File Lifecycle',
                        'items' => [
                            ['label' => 'File', 'before' => $this->displayValue($file->original_name), 'after' => 'Deleted'],
                        ],
                    ]],
                ],
            );

            return $this->buildPayload($inventoryItem);
        });
    }

    public function importInspectionPhotos(string $actorUserId, string $inventoryItemId, string $inspectionId): array
    {
        return DB::transaction(function () use ($actorUserId, $inventoryItemId, $inspectionId) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);
            $this->assertInventoryItemAllowsMutation($inventoryItem);

            $inspection = $this->inspections->findOrFail($inspectionId);
            $photos = $inspection->relationLoaded('photos')
                ? $inspection->photos
                : $inspection->photos()->whereNull('deleted_at')->get();

            $googlePhotos = $photos
                ->filter(fn (InspectionPhoto $photo): bool => (string) $photo->driver === 'google' && trim((string) $photo->path) !== '');

            if ($googlePhotos->isEmpty()) {
                throw ValidationException::withMessages([
                    'inspection_id' => ['The selected inspection does not have importable Google Drive photos.'],
                ]);
            }

            $folderId = $this->ensureDriveFolder($inventoryItem, $inspection->po_number);
            $createdFiles = [];

            foreach ($googlePhotos as $photo) {
                $copied = $this->driveFiles->copyFile(
                    sourceFileId: (string) $photo->path,
                    newName: $this->nullableString($photo->original_name),
                    targetFolderId: $folderId,
                );

                $createdFiles[] = $this->files->create([
                    'inventory_item_id' => (string) $inventoryItem->id,
                    'driver' => 'google',
                    'path' => (string) ($copied['drive_file_id'] ?? ''),
                    'type' => InventoryFileTypes::PHOTO,
                    'is_primary' => false,
                    'position' => $this->files->nextPositionForInventoryItem((string) $inventoryItem->id),
                    'original_name' => $this->nullableString($photo->original_name),
                    'mime' => $this->nullableString($photo->mime) ?? $this->nullableString($copied['mime_type'] ?? null),
                    'size' => $photo->size ?? ($copied['size'] ?? null),
                    'caption' => $this->nullableString($photo->caption),
                ]);
            }

            $referenceNo = $this->inspectionReference($inspection->po_number, $inspection->dv_number);
            $this->events->create($actorUserId, (string) $inventoryItem->id, [
                'event_type' => InventoryEventTypes::CREATED_FROM_INSPECTION,
                'event_date' => now()->toDateTimeString(),
                'department_id' => $inspection->department_id ?: $inventoryItem->department_id,
                'person_accountable' => $inspection->accountable_officer ?: $inventoryItem->accountable_officer,
                'status' => $inventoryItem->status,
                'condition' => $inspection->condition ?: $inventoryItem->condition,
                'reference_type' => 'Inspection',
                'reference_no' => $referenceNo,
                'reference_id' => (string) $inspection->id,
                'notes' => 'Inspection evidence copied into inventory file history.',
            ]);

            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);

            $this->auditLogs->record(
                action: 'gso.inventory-item.file.imported-from-inspection',
                subject: $inventoryItem,
                changesOld: [],
                changesNew: [
                    'inspection_id' => (string) $inspection->id,
                    'copied_count' => count($createdFiles),
                    'file_ids' => collect($createdFiles)->map(fn (InventoryItemFile $createdFile) => (string) $createdFile->id)->values()->all(),
                ],
                meta: ['actor_user_id' => $actorUserId],
                message: 'Inspection photos imported into inventory item: ' . $this->inventoryItemLabel($inventoryItem),
                display: [
                    'summary' => 'Inspection photos imported: ' . $this->inventoryItemLabel($inventoryItem),
                    'subject_label' => $this->inventoryItemLabel($inventoryItem),
                    'sections' => [[
                        'title' => 'Inspection Bridge',
                        'items' => [
                            ['label' => 'Source Inspection', 'before' => 'None', 'after' => $referenceNo ?? 'Inspection'],
                            ['label' => 'Files Copied', 'before' => '0', 'after' => (string) count($createdFiles)],
                        ],
                    ]],
                ],
            );

            return $this->buildPayload($inventoryItem);
        });
    }

    public function preview(string $inventoryItemId, string $fileId): array
    {
        $file = $this->files->findForInventoryItemOrFail($inventoryItemId, $fileId);

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

    /**
     * @return array{inventory_item: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    private function buildPayload(InventoryItem $inventoryItem): array
    {
        $files = $this->files->listForInventoryItem((string) $inventoryItem->id)
            ->map(fn (InventoryItemFile $file): array => $this->mapFile($file))
            ->values()
            ->all();

        return [
            'inventory_item' => [
                'id' => (string) $inventoryItem->id,
                'label' => $this->inventoryItemLabel($inventoryItem),
                'property_number' => $this->nullableString($inventoryItem->property_number),
                'po_number' => $this->nullableString($inventoryItem->po_number),
                'file_count' => count($files),
                'is_archived' => $inventoryItem->trashed(),
                'drive_folder_id' => $this->nullableString($inventoryItem->drive_folder_id),
            ],
            'files' => $files,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapFile(InventoryItemFile $file): array
    {
        return [
            'id' => (string) $file->id,
            'inventory_item_id' => (string) $file->inventory_item_id,
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
            'preview_url' => route('gso.inventory-items.files.preview', [
                'inventoryItem' => $file->inventory_item_id,
                'file' => $file->id,
            ]),
            'is_image' => str_starts_with((string) ($file->mime ?? ''), 'image/'),
            'created_at' => $file->created_at?->toDateTimeString(),
            'created_at_text' => $file->created_at?->format('M d, Y h:i A') ?? '-',
        ];
    }

    private function assertInventoryItemAllowsMutation(InventoryItem $inventoryItem): void
    {
        if ($inventoryItem->trashed()) {
            throw ValidationException::withMessages([
                'inventory_item' => ['Archived inventory items cannot be modified.'],
            ]);
        }
    }

    private function ensureDriveFolder(InventoryItem $inventoryItem, mixed $fallbackName = null): string
    {
        $existingFolderId = trim((string) ($inventoryItem->drive_folder_id ?? ''));

        if ($existingFolderId !== '') {
            return $existingFolderId;
        }

        $baseFolderId = trim((string) config(
            'gso.storage.inventory_files_folder_id',
            config('services.google_drive.folder_id', '')
        ));

        if ($baseFolderId === '') {
            throw new RuntimeException('GSO inventory files folder is not configured.');
        }

        $folderName = $this->nullableString($inventoryItem->po_number)
            ?? $this->nullableString($fallbackName)
            ?? $this->nullableString($inventoryItem->property_number);

        if ($folderName === null) {
            throw ValidationException::withMessages([
                'po_number' => ['PO number or reference is required before uploading inventory files.'],
            ]);
        }

        $folder = $this->driveFolders->ensureFolder($folderName, $baseFolderId);
        $folderId = trim((string) ($folder['drive_folder_id'] ?? ''));

        if ($folderId === '') {
            throw new RuntimeException('Failed to resolve the Google Drive folder for inventory files.');
        }

        $inventoryItem->drive_folder_id = $folderId;
        $this->inventoryItems->save($inventoryItem);

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

    /**
     * @param  array<int, InventoryItemFile>  $files
     */
    private function fileNamesLabel(array $files): string
    {
        $names = collect($files)
            ->map(fn (InventoryItemFile $file) => $this->displayValue($file->original_name))
            ->filter(fn (string $name) => $name !== 'None')
            ->take(5)
            ->implode(', ');

        return $names !== '' ? $names : 'Uploaded file(s)';
    }

    private function inspectionReference(mixed $poNumber, mixed $dvNumber): ?string
    {
        $poNumber = trim((string) ($poNumber ?? ''));
        $dvNumber = trim((string) ($dvNumber ?? ''));

        if ($poNumber !== '' && $dvNumber !== '') {
            return "PO {$poNumber} / DV {$dvNumber}";
        }

        if ($poNumber !== '') {
            return "PO {$poNumber}";
        }

        if ($dvNumber !== '') {
            return "DV {$dvNumber}";
        }

        return null;
    }

    private function deleteStoredFile(InventoryItemFile $file): void
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
                // Local cleanup is best-effort.
            }
        }
    }

    private function inventoryItemLabel(InventoryItem $inventoryItem): string
    {
        $item = $inventoryItem->relationLoaded('item') ? $inventoryItem->item : null;
        $itemName = trim((string) ($item?->item_name ?? ''));
        $propertyNumber = trim((string) ($inventoryItem->property_number ?? ''));

        if ($itemName !== '' && $propertyNumber !== '') {
            return "{$itemName} ({$propertyNumber})";
        }

        return $itemName !== '' ? $itemName : ($propertyNumber !== '' ? $propertyNumber : 'Inventory Item');
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
