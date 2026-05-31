<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\AirItemImage;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AirInspectionItemImageService
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirItemRepositoryInterface $airItems,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
        private readonly GsoStorageSettingsServiceInterface $storageSettings,
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, AirItemImage>
     */
    public function upload(
        string $actorUserId,
        string $airId,
        string $lineId,
        array $files,
    ): array {
        [$air, $airItem] = $this->resolveLineage($airId, $lineId);
        $this->assertUsesItemLevelInspectionImages($airItem);

        return DB::transaction(
            function () use ($actorUserId, $air, $airItem, $files): array {
                $folderId = $this->ensureDriveFolder($air, $airItem);
                $uploadedDriveFileIds = [];
                $createdImages = [];

                try {
                    foreach ($files as $file) {
                        if (!$file instanceof UploadedFile || !$file->isValid()) {
                            continue;
                        }

                        $this->assertImageUpload($file);

                        $uploaded = $this->driveFiles->upload(
                            file: $file,
                            name: null,
                            makePublic: false,
                            folderId: $folderId,
                        );

                        $driveFileId = trim(
                            (string) ($uploaded['drive_file_id'] ?? ''),
                        );
                        $uploadedDriveFileIds[] = $driveFileId;

                        $createdImages[] = AirItemImage::query()->create([
                            'air_item_id' => (string) $airItem->id,
                            'storage_provider' => 'google',
                            'storage_disk' => 'google_drive',
                            'storage_path' => $driveFileId,
                            'external_file_id' => $driveFileId !== ''
                                ? $driveFileId
                                : null,
                            'original_name' => $this->nullableString(
                                $file->getClientOriginalName(),
                            ),
                            'stored_name' => $this->nullableString(
                                $file->getClientOriginalName(),
                            ),
                            'mime_type' => $this->nullableString(
                                $file->getMimeType(),
                            ) ??
                                $this->nullableString(
                                    $uploaded['mime_type'] ?? null,
                                ),
                            'size_bytes' => $file->getSize(),
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

                if ($createdImages === []) {
                    throw ValidationException::withMessages([
                        'images' => ['At least one valid image is required.'],
                    ]);
                }

                $this->auditLogs->record(
                    action: 'gso.air.inspection.item-image.uploaded',
                    subject: $airItem,
                    changesOld: [],
                    changesNew: [
                        'uploaded_count' => count($createdImages),
                        'image_ids' => collect($createdImages)
                            ->map(
                                fn(
                                    AirItemImage $image,
                                ) => (string) $image->id,
                            )
                            ->values()
                            ->all(),
                    ],
                    meta: [
                        'actor_user_id' => $actorUserId,
                        'air_id' => (string) $air->id,
                    ],
                    message:
                        'AIR item images uploaded: ' .
                        $this->airItemLabel($airItem),
                    display: [
                        'summary' =>
                            'AIR item images uploaded: ' .
                            $this->airItemLabel($airItem),
                        'subject_label' => $this->airItemLabel($airItem),
                        'sections' => [[
                            'title' => 'Image Upload',
                            'items' => [
                                [
                                    'label' => 'AIR',
                                    'before' => 'None',
                                    'after' => $this->airLabel($air),
                                ],
                                [
                                    'label' => 'Uploaded Count',
                                    'before' => '0',
                                    'after' => (string) count($createdImages),
                                ],
                                [
                                    'label' => 'Files',
                                    'before' => 'None',
                                    'after' => $this->fileNamesLabel(
                                        $createdImages,
                                    ),
                                ],
                            ],
                        ]],
                    ],
                );

                return $createdImages;
            },
        );
    }

    public function delete(
        string $actorUserId,
        string $airId,
        string $lineId,
        string $imageId,
    ): void {
        [$air, $airItem] = $this->resolveLineage($airId, $lineId);
        $this->assertUsesItemLevelInspectionImages($airItem);

        DB::transaction(function () use (
            $actorUserId,
            $air,
            $airItem,
            $imageId,
        ): void {
            $image = $this->findImageForItemOrFail(
                (string) $airItem->id,
                $imageId,
            );
            $before = $this->snapshotImage($image);

            $this->deleteStoredFile($image);
            $image->delete();

            $this->auditLogs->record(
                action: 'gso.air.inspection.item-image.deleted',
                subject: $airItem,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: [
                    'actor_user_id' => $actorUserId,
                    'air_id' => (string) $air->id,
                ],
                message:
                    'AIR item image deleted: ' .
                    $this->airItemLabel($airItem),
                display: [
                    'summary' =>
                        'AIR item image deleted: ' .
                        $this->airItemLabel($airItem),
                    'subject_label' => $this->airItemLabel($airItem),
                    'sections' => [[
                        'title' => 'File Lifecycle',
                        'items' => [
                            [
                                'label' => 'AIR',
                                'before' => $this->airLabel($air),
                                'after' => $this->airLabel($air),
                            ],
                            [
                                'label' => 'File',
                                'before' => $this->displayValue(
                                    $image->original_name,
                                ),
                                'after' => 'Deleted',
                            ],
                        ],
                    ]],
                ],
            );
        });
    }

    /**
     * @return array{0: Air, 1: AirItem}
     */
    private function resolveLineage(string $airId, string $lineId): array
    {
        $air = $this->airs->findOrFail($airId, true);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Archived AIR records cannot manage item images.'],
            ]);
        }

        if (
            !in_array(
                (string) ($air->status ?? ''),
                [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'status' => [
                    'This AIR is not in a state that supports item image work.',
                ],
            ]);
        }

        $airItem = $this->airItems->findOrFail($lineId);
        if ((string) $airItem->air_id !== (string) $air->id) {
            throw ValidationException::withMessages([
                'air_item' => [
                    'The selected AIR item does not belong to this AIR.',
                ],
            ]);
        }

        return [$air, $airItem];
    }

    private function assertUsesItemLevelInspectionImages(AirItem $airItem): void
    {
        if ($this->requiresSerializedInspectionUnits($airItem)) {
            throw ValidationException::withMessages([
                'air_item' => [
                    'This AIR line stores inspection images on inspection units instead of the AIR item record.',
                ],
            ]);
        }
    }

    private function requiresSerializedInspectionUnits(AirItem $airItem): bool
    {
        $trackingType = strtolower(
            trim((string) ($airItem->tracking_type_snapshot ?? '')),
        );

        return $trackingType === 'property' ||
            (bool) ($airItem->requires_serial_snapshot ?? false) ||
            (bool) ($airItem->is_semi_expendable_snapshot ?? false);
    }

    private function assertImageUpload(UploadedFile $file): void
    {
        $mime = (string) ($file->getMimeType() ?? '');

        if (!str_starts_with($mime, 'image/')) {
            throw ValidationException::withMessages([
                'images' => [
                    'Only image uploads are supported for AIR item inspection evidence.',
                ],
            ]);
        }
    }

    private function ensureDriveFolder(Air $air, AirItem $airItem): string
    {
        $baseFolderId = trim(
            (string) ($this->storageSettings->inspectionPhotosFolderId() ?? ''),
        );

        if ($baseFolderId === '') {
            throw new RuntimeException(
                'GSO AIR item image folder is not configured.',
            );
        }

        $poNumber = trim((string) ($air->po_number ?? ''));
        if ($poNumber === '') {
            throw ValidationException::withMessages([
                'po_number' => [
                    'PO number is required before uploading AIR item images.',
                ],
            ]);
        }

        $folder = $this->driveFolders->ensureFolder(
            $poNumber . ' - ' . $this->airItemLabel($airItem),
            $baseFolderId,
        );
        $folderId = trim((string) ($folder['drive_folder_id'] ?? ''));

        if ($folderId === '') {
            throw new RuntimeException(
                'Failed to resolve the Google Drive folder for AIR item images.',
            );
        }

        return $folderId;
    }

    private function findImageForItemOrFail(
        string $airItemId,
        string $imageId,
    ): AirItemImage {
        return AirItemImage::query()
            ->where('air_item_id', $airItemId)
            ->findOrFail($imageId);
    }

    private function deleteStoredFile(AirItemImage $image): void
    {
        $provider = trim((string) ($image->storage_provider ?? ''));
        $externalFileId = trim((string) ($image->external_file_id ?? ''));
        $storagePath = trim((string) ($image->storage_path ?? ''));

        if ($provider === 'google') {
            $target = $externalFileId !== '' ? $externalFileId : $storagePath;
            if ($target !== '') {
                $this->driveFiles->deleteFile($target);
            }

            return;
        }

        if ($storagePath !== '') {
            Storage::disk(
                trim((string) ($image->storage_disk ?? 'public')) ?: 'public',
            )->delete($storagePath);
        }
    }

    /**
     * @param  array<int, AirItemImage>  $images
     */
    private function fileNamesLabel(array $images): string
    {
        $names = collect($images)
            ->map(
                fn(AirItemImage $image) => $this->displayValue(
                    $image->original_name,
                ),
            )
            ->filter(fn(string $name) => $name !== 'None')
            ->take(5)
            ->implode(', ');

        return $names !== '' ? $names : 'Uploaded image(s)';
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotImage(AirItemImage $image): array
    {
        return [
            'storage_provider' => $this->nullableString(
                $image->storage_provider,
            ),
            'storage_path' => $this->nullableString($image->storage_path),
            'external_file_id' => $this->nullableString(
                $image->external_file_id,
            ),
            'original_name' => $this->nullableString($image->original_name),
            'stored_name' => $this->nullableString($image->stored_name),
            'mime_type' => $this->nullableString($image->mime_type),
            'size_bytes' => $image->size_bytes !== null
                ? (int) $image->size_bytes
                : null,
        ];
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

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ??
            '';

        return $value !== '' ? $value : null;
    }
}
