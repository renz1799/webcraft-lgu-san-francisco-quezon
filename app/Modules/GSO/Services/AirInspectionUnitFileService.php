<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\AirItemUnit;
use App\Modules\GSO\Models\AirItemUnitFile;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AirInspectionUnitFileServiceInterface;
use App\Modules\GSO\Support\AirStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryFileTypes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AirInspectionUnitFileService implements AirInspectionUnitFileServiceInterface
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirItemRepositoryInterface $airItems,
        private readonly AirItemUnitRepositoryInterface $units,
        private readonly AirItemUnitFileRepositoryInterface $files,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
    ) {}

    public function listForUnit(string $airId, string $airItemId, string $unitId): array
    {
        [$air, $airItem, $unit] = $this->resolveLineage($airId, $airItemId, $unitId, false);

        return $this->buildPayload($air, $airItem, $unit);
    }

    public function upload(
        string $actorUserId,
        string $airId,
        string $airItemId,
        string $unitId,
        array $files,
        ?string $type = null,
        ?string $caption = null,
    ): array
    {
        [$air, $airItem, $unit] = $this->resolveLineage($airId, $airItemId, $unitId, true);

        DB::transaction(function () use ($actorUserId, $air, $airItem, $unit, $files, $type, $caption): void {
            $folderId = $this->ensureDriveFolder($air, $airItem, $unit);
            $uploadedDriveFileIds = [];
            $createdFiles = [];
            $hasPrimary = $this->files->listForUnit((string) $unit->id)
                ->contains(fn (AirItemUnitFile $file): bool => (bool) $file->is_primary);

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
                        'air_item_unit_id' => (string) $unit->id,
                        'driver' => 'google',
                        'path' => (string) ($uploaded['drive_file_id'] ?? ''),
                        'type' => $resolvedType,
                        'is_primary' => ! $hasPrimary && count($createdFiles) === 0,
                        'position' => $this->files->nextPositionForUnit((string) $unit->id),
                        'original_name' => $this->nullableString($file->getClientOriginalName()),
                        'mime' => $this->nullableString($file->getMimeType())
                            ?? $this->nullableString($uploaded['mime_type'] ?? null),
                        'size' => $file->getSize(),
                        'caption' => $this->nullableString($caption),
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
                    'files' => ['At least one valid image is required.'],
                ]);
            }

            $this->auditLogs->record(
                action: 'gso.air.inspection.unit-file.uploaded',
                subject: $unit,
                changesOld: [],
                changesNew: [
                    'uploaded_count' => count($createdFiles),
                    'file_ids' => collect($createdFiles)->map(fn (AirItemUnitFile $createdFile) => (string) $createdFile->id)->values()->all(),
                ],
                meta: ['actor_user_id' => $actorUserId, 'air_item_id' => (string) $airItem->id],
                message: 'AIR unit images uploaded: ' . $this->unitLabel($unit),
                display: [
                    'summary' => 'AIR unit images uploaded: ' . $this->unitLabel($unit),
                    'subject_label' => $this->unitLabel($unit),
                    'sections' => [[
                        'title' => 'Image Upload',
                        'items' => [
                            ['label' => 'AIR', 'before' => 'None', 'after' => $this->airLabel($air)],
                            ['label' => 'Uploaded Count', 'before' => '0', 'after' => (string) count($createdFiles)],
                            ['label' => 'Files', 'before' => 'None', 'after' => $this->fileNamesLabel($createdFiles)],
                        ],
                    ]],
                ],
            );
        });

        return $this->buildPayload($air, $airItem, $unit);
    }

    public function preview(string $airId, string $airItemId, string $unitId, string $fileId): array
    {
        [, , $unit] = $this->resolveLineage($airId, $airItemId, $unitId, false);
        $file = $this->files->findForUnitOrFail((string) $unit->id, $fileId);

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

    public function delete(string $actorUserId, string $airId, string $airItemId, string $unitId, string $fileId): array
    {
        [$air, $airItem, $unit] = $this->resolveLineage($airId, $airItemId, $unitId, true);

        DB::transaction(function () use ($actorUserId, $air, $airItem, $unit, $fileId): void {
            $file = $this->files->findForUnitOrFail((string) $unit->id, $fileId);
            $before = $this->mapFile($file, $air, $airItem, $unit);

            $this->deleteStoredFile($file);
            $this->files->delete($file);

            $replacementPrimary = $this->files->listForUnit((string) $unit->id)->first();
            if ($file->is_primary && $replacementPrimary instanceof AirItemUnitFile) {
                $replacementPrimary->is_primary = true;
                $this->files->save($replacementPrimary);
            }

            $this->auditLogs->record(
                action: 'gso.air.inspection.unit-file.deleted',
                subject: $unit,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId, 'air_item_id' => (string) $airItem->id],
                message: 'AIR unit image deleted: ' . $this->unitLabel($unit),
                display: [
                    'summary' => 'AIR unit image deleted: ' . $this->unitLabel($unit),
                    'subject_label' => $this->unitLabel($unit),
                    'sections' => [[
                        'title' => 'File Lifecycle',
                        'items' => [
                            ['label' => 'AIR', 'before' => $this->airLabel($air), 'after' => $this->airLabel($air)],
                            ['label' => 'File', 'before' => $this->displayValue($file->original_name), 'after' => 'Deleted'],
                        ],
                    ]],
                ],
            );
        });

        return $this->buildPayload($air, $airItem, $unit);
    }

    public function setPrimary(string $actorUserId, string $airId, string $airItemId, string $unitId, string $fileId): array
    {
        [$air, $airItem, $unit] = $this->resolveLineage($airId, $airItemId, $unitId, true);

        DB::transaction(function () use ($actorUserId, $air, $airItem, $unit, $fileId): void {
            $file = $this->files->findForUnitOrFail((string) $unit->id, $fileId);
            $before = $this->mapFile($file, $air, $airItem, $unit);

            $this->files->clearPrimaryForUnit((string) $unit->id, (string) $file->id);
            $file->is_primary = true;
            $this->files->save($file);

            $this->auditLogs->record(
                action: 'gso.air.inspection.unit-file.primary-set',
                subject: $unit,
                changesOld: $before,
                changesNew: ['is_primary' => true],
                meta: ['actor_user_id' => $actorUserId, 'air_item_id' => (string) $airItem->id],
                message: 'AIR unit image marked as primary: ' . $this->unitLabel($unit),
                display: [
                    'summary' => 'AIR unit image marked as primary: ' . $this->unitLabel($unit),
                    'subject_label' => $this->unitLabel($unit),
                    'sections' => [[
                        'title' => 'Primary File',
                        'items' => [
                            ['label' => 'AIR', 'before' => $this->airLabel($air), 'after' => $this->airLabel($air)],
                            ['label' => 'File', 'before' => $this->displayValue($file->original_name), 'after' => $this->displayValue($file->original_name)],
                            ['label' => 'Primary', 'before' => 'No', 'after' => 'Yes'],
                        ],
                    ]],
                ],
            );
        });

        return $this->buildPayload($air, $airItem, $unit);
    }

    /**
     * @return array{0: Air, 1: AirItem, 2: AirItemUnit}
     */
    private function resolveLineage(string $airId, string $airItemId, string $unitId, bool $requireEditable): array
    {
        $air = $this->airs->findOrFail($airId, true);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Archived AIR records cannot manage unit files.'],
            ]);
        }

        $allowedStatuses = $requireEditable
            ? [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS]
            : [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS, AirStatuses::INSPECTED];

        if (! in_array((string) ($air->status ?? ''), $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => ['This AIR is not in a state that supports unit file work.'],
            ]);
        }

        $airItem = $this->airItems->findOrFail($airItemId);

        if ((string) $airItem->air_id !== (string) $air->id) {
            throw ValidationException::withMessages([
                'air_item' => ['The selected AIR item does not belong to this AIR.'],
            ]);
        }

        $unit = $this->units->findForAirItemOrFail((string) $airItem->id, $unitId);

        return [$air, $airItem, $unit];
    }

    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, unit: array<string, mixed>, files: array<int, array<string, mixed>>}
     */
    private function buildPayload(Air $air, AirItem $airItem, AirItemUnit $unit): array
    {
        $files = $this->files->listForUnit((string) $unit->id)
            ->map(fn (AirItemUnitFile $file): array => $this->mapFile($file, $air, $airItem, $unit))
            ->values()
            ->all();

        return [
            'air' => [
                'id' => (string) $air->id,
                'label' => $this->airLabel($air),
                'status' => (string) ($air->status ?? ''),
                'status_text' => AirStatuses::label((string) ($air->status ?? '')),
                'po_number' => $this->nullableString($air->po_number),
            ],
            'air_item' => [
                'id' => (string) $airItem->id,
                'label' => $this->airItemLabel($airItem),
            ],
            'unit' => [
                'id' => (string) $unit->id,
                'label' => $this->unitLabel($unit),
                'serial_number' => $this->nullableString($unit->serial_number),
                'property_number' => $this->nullableString($unit->property_number),
                'condition_status' => $this->nullableString($unit->condition_status),
                'condition_status_text' => InventoryConditions::labels()[(string) ($unit->condition_status ?? '')] ?? 'Unknown',
                'drive_folder_id' => $this->nullableString($unit->drive_folder_id),
                'file_count' => count($files),
            ],
            'files' => $files,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapFile(AirItemUnitFile $file, Air $air, AirItem $airItem, AirItemUnit $unit): array
    {
        return [
            'id' => (string) $file->id,
            'air_item_unit_id' => (string) $file->air_item_unit_id,
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
            'preview_url' => route('gso.air.inspection.unit-files.preview', [
                'air' => $air->id,
                'airItem' => $airItem->id,
                'unit' => $unit->id,
                'file' => $file->id,
            ]),
            'is_image' => str_starts_with((string) ($file->mime ?? ''), 'image/'),
            'created_at' => $file->created_at?->toDateTimeString(),
            'created_at_text' => $file->created_at?->format('M d, Y h:i A') ?? '-',
        ];
    }

    private function ensureDriveFolder(Air $air, AirItem $airItem, AirItemUnit $unit): string
    {
        $existingFolderId = trim((string) ($unit->drive_folder_id ?? ''));

        if ($existingFolderId !== '') {
            return $existingFolderId;
        }

        $baseFolderId = trim((string) config(
            'gso.storage.air_unit_files_folder_id',
            config('services.google_drive.folder_id', '')
        ));

        if ($baseFolderId === '') {
            throw new RuntimeException('GSO AIR unit file folder is not configured.');
        }

        $poNumber = trim((string) ($air->po_number ?? ''));

        if ($poNumber === '') {
            throw ValidationException::withMessages([
                'po_number' => ['PO number is required before uploading AIR unit images.'],
            ]);
        }

        $folderName = $poNumber . ' - ' . $this->airItemLabel($airItem) . ' - ' . $this->unitLabel($unit);
        $folder = $this->driveFolders->ensureFolder($folderName, $baseFolderId);
        $folderId = trim((string) ($folder['drive_folder_id'] ?? ''));

        if ($folderId === '') {
            throw new RuntimeException('Failed to resolve the Google Drive folder for AIR unit images.');
        }

        $unit->drive_folder_id = $folderId;
        $this->units->save($unit);

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

            if (! in_array($type, InventoryFileTypes::airImageValues(), true)) {
                throw ValidationException::withMessages([
                    'type' => ['Image type is invalid.'],
                ]);
            }

            return $type;
        }

        $mime = (string) ($file->getMimeType() ?? '');

        if (str_starts_with($mime, 'image/')) {
            return InventoryFileTypes::PHOTO;
        }

        throw ValidationException::withMessages([
            'files' => ['Only image uploads are supported for AIR inspection units.'],
        ]);
    }

    private function deleteStoredFile(AirItemUnitFile $file): void
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
     * @param  array<int, AirItemUnitFile>  $files
     */
    private function fileNamesLabel(array $files): string
    {
        $names = collect($files)
            ->map(fn (AirItemUnitFile $file) => $this->displayValue($file->original_name))
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

    private function airItemLabel(AirItem $airItem): string
    {
        $itemName = trim((string) ($airItem->item_name_snapshot ?? ''));
        $stockNo = trim((string) ($airItem->stock_no_snapshot ?? ''));

        if ($itemName !== '' && $stockNo !== '') {
            return "{$itemName} ({$stockNo})";
        }

        return $itemName !== '' ? $itemName : ($stockNo !== '' ? $stockNo : 'AIR Item');
    }

    private function unitLabel(AirItemUnit $unit): string
    {
        $serial = trim((string) ($unit->serial_number ?? ''));
        $property = trim((string) ($unit->property_number ?? ''));
        $brand = trim((string) ($unit->brand ?? ''));
        $model = trim((string) ($unit->model ?? ''));

        if ($property !== '' && $serial !== '') {
            return "{$property} / {$serial}";
        }

        if ($serial !== '') {
            return $serial;
        }

        if ($property !== '') {
            return $property;
        }

        if ($brand !== '' && $model !== '') {
            return "{$brand} {$model}";
        }

        if ($brand !== '') {
            return $brand;
        }

        if ($model !== '') {
            return $model;
        }

        return 'Inspection Unit';
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
