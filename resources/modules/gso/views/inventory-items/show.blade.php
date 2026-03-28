@extends('layouts.master')

@php
    use App\Modules\GSO\Support\InventoryConditions;
    use App\Modules\GSO\Support\InventoryCustodyStates;
    use App\Modules\GSO\Support\InventoryEventTypes;
    use App\Modules\GSO\Support\InventoryStatuses;
    use Illuminate\Support\Str;

    $canManageInventoryItems = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Inventory Items');

    $photoFiles = collect($inventoryItem->files ?? [])
        ->sortBy(fn ($file) => sprintf(
            '%d-%05d-%012d',
            $file->is_primary ? 0 : 1,
            (int) ($file->position ?? 9999),
            $file->created_at?->timestamp ?? PHP_INT_MAX
        ))
        ->values();

    $photoUrl = static fn ($file) => route('gso.inventory-items.files.preview', [
        'inventoryItem' => $inventoryItem->id,
        'file' => $file->id,
    ]);

    $primaryPhoto = $photoFiles->first();
    $primaryPhotoUrl = $primaryPhoto ? $photoUrl($primaryPhoto) : null;
    $primaryPhotoLabel = $primaryPhoto?->original_name ?: 'Inventory photo';
    $galleryPhotoFiles = $photoFiles
        ->values()
        ->map(fn ($photo, $index) => [
            'id' => (string) $photo->id,
            'url' => $photoUrl($photo),
            'name' => $photo->original_name ?: (($inventoryItem->item?->item_name ?? 'Inventory photo') . ' ' . ($index + 1)),
            'is_primary' => (bool) $photo->is_primary,
        ])
        ->all();

    $itemName = $inventoryItem->item?->item_name
        ?? $inventoryItem->item_name
        ?? $inventoryItem->description
        ?? 'Inventory Item';

    $descriptionText = trim((string) (
        $inventoryItem->description
        ?: $inventoryItem->item?->description
        ?: 'No description has been recorded for this asset yet.'
    ));

    $currency = static fn ($value) => is_numeric($value) ? 'P' . number_format((float) $value, 2) : '-';
    $headline = static fn ($value) => filled($value) ? Str::headline((string) $value) : '-';

    $trackingLabel = match (true) {
        (bool) $inventoryItem->is_ics => 'ICS Asset',
        ($inventoryItem->item?->tracking_type ?? null) === 'property' => 'Property Asset',
        ($inventoryItem->item?->tracking_type ?? null) === 'semi_expendable' => 'Semi-Expendable',
        ($inventoryItem->item?->tracking_type ?? null) === 'consumable' => 'Consumable',
        default => 'Inventory Record',
    };

    $departmentLabel = collect([$inventoryItem->department?->code, $inventoryItem->department?->name])->filter()->implode(' - ') ?: '-';
    $accountableOfficerLabel = $inventoryItem->accountableOfficerRelation?->full_name ?: $inventoryItem->accountable_officer ?: '-';
    $fundSourceLabel = collect([$inventoryItem->fundSource?->code, $inventoryItem->fundSource?->name])->filter()->implode(' - ') ?: '-';
    $assetCategoryLabel = $inventoryItem->item?->asset?->asset_name ?: '-';
    $majorSubAccountLabel = $inventoryItem->item?->major_sub_account_group ?: '-';
    $acquisitionDateLabel = $inventoryItem->acquisition_date?->format('F d, Y') ?: '-';
    $quantityValue = filled($inventoryItem->quantity)
        ? rtrim(rtrim(number_format((float) $inventoryItem->quantity, 2, '.', ''), '0'), '.')
        : '-';
    $quantityUnitLabel = trim(collect([$quantityValue !== '-' ? $quantityValue : null, $inventoryItem->unit])->filter()->implode(' ')) ?: '-';

    $detailRows = [
        ['label' => 'Asset Category', 'value' => $assetCategoryLabel],
        ['label' => 'Property Number', 'value' => $inventoryItem->property_number ?: '-'],
        ['label' => 'Stock Number', 'value' => $inventoryItem->stock_number ?: '-'],
        ['label' => 'PO Number', 'value' => $inventoryItem->po_number ?: '-'],
        ['label' => 'Brand', 'value' => $inventoryItem->brand ?: '-'],
        ['label' => 'Model', 'value' => $inventoryItem->model ?: '-'],
        ['label' => 'Serial Number', 'value' => $inventoryItem->serial_number ?: '-'],
        ['label' => 'Department', 'value' => $departmentLabel],
        ['label' => 'Accountable Officer', 'value' => $accountableOfficerLabel],
        ['label' => 'Fund Source', 'value' => $fundSourceLabel],
        ['label' => 'Acquisition Date', 'value' => $acquisitionDateLabel],
        ['label' => 'Quantity / Unit', 'value' => $quantityUnitLabel],
        ['label' => 'Major Sub Account', 'value' => $majorSubAccountLabel],
        ['label' => 'Service Life', 'value' => filled($inventoryItem->service_life) ? $inventoryItem->service_life . ' year(s)' : '-'],
    ];

    $remarksText = trim((string) ($inventoryItem->remarks ?? ''));
    $historyEvents = collect($inventoryItem->events ?? [])
        ->sortByDesc(fn ($event) => $event->event_date?->timestamp ?? $event->created_at?->timestamp ?? 0)
        ->values();
    $linkedRecords = collect($linkedRecords ?? []);

    $referenceUrlFor = static function ($referenceType, $referenceId) {
        $type = strtolower(trim((string) $referenceType));
        $id = trim((string) $referenceId);
        if ($type === '' || $id === '') {
            return null;
        }

        return match ($type) {
            'air' => route('gso.air.inspect', ['air' => $id]),
            'inspection' => route('gso.inspections.show', ['inspection' => $id]),
            'task', 'workflow task' => route('gso.tasks.show', ['id' => $id]),
            'par' => route('gso.pars.show', ['par' => $id]),
            'ics' => route('gso.ics.edit', ['ics' => $id]),
            'itr' => route('gso.itrs.edit', ['itr' => $id]),
            'ptr' => route('gso.ptrs.edit', ['ptr' => $id]),
            'wmr' => route('gso.wmrs.edit', ['wmr' => $id]),
            'ris' => route('gso.ris.edit', ['ris' => $id]),
            default => null,
        };
    };
@endphp

@section('styles')
    <style>
        .inventory-show-gallery { display: grid; gap: 1rem; min-width: 0; width: 100%; }
        .inventory-show-preview-frame {
            position: relative;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 1rem;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(59,130,246,.06), rgba(15,23,42,.08));
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }
        .inventory-show-preview-image-wrap {
            min-height: 23rem;
            height: 23rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(248, 250, 252, .9);
        }
        .inventory-show-preview-image-wrap img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 23rem;
            object-fit: contain;
            display: block;
        }
        .inventory-show-gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(148, 163, 184, .25);
            background: rgba(255, 255, 255, .92);
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
        }
        .inventory-show-gallery-nav:hover { background: #fff; }
        .inventory-show-gallery-nav[data-gallery-nav="prev"] { left: .75rem; }
        .inventory-show-gallery-nav[data-gallery-nav="next"] { right: .75rem; }
        .inventory-show-thumb-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
            width: 100%;
        }
        .inventory-show-thumb {
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: .75rem;
            overflow: hidden;
            background: rgba(248, 250, 252, .85);
            padding: 0;
            aspect-ratio: 1 / 1;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .inventory-show-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .inventory-show-thumb:hover,
        .inventory-show-thumb.is-active {
            border-color: rgba(99, 102, 241, .45);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .14);
        }
        .inventory-show-empty { min-height: 23rem; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; color: #64748b; }
        .inventory-show-history { max-height: 42rem; overflow-y: auto; padding-right: .25rem; }
        .inventory-show-summary { display: grid; gap: 1rem; }
        .inventory-edit-photo-card {
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 1rem;
            overflow: hidden;
            background: rgba(255, 255, 255, .95);
        }
        .inventory-edit-photo-card-image {
            width: 100%;
            height: 11rem;
            object-fit: cover;
            display: block;
            background: rgba(248, 250, 252, .9);
        }
        .inventory-edit-photo-card-body {
            padding: .875rem;
        }
        .inventory-edit-photo-card-name {
            font-size: .8125rem;
            line-height: 1.4;
            color: #334155;
            word-break: break-word;
        }
        .inventory-edit-photo-empty {
            border: 1px dashed rgba(148, 163, 184, .35);
            border-radius: 1rem;
            padding: 1rem;
            text-align: center;
            color: #64748b;
            font-size: .8125rem;
        }
        .inventory-edit-photo-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: .2rem .55rem;
            font-size: .6875rem;
            font-weight: 600;
            background: rgba(59, 130, 246, .1);
            color: #2563eb;
        }
        .inventory-edit-photo-notice-success {
            background: rgba(34, 197, 94, .12);
            color: #15803d;
        }
        .inventory-edit-photo-notice-error {
            background: rgba(239, 68, 68, .12);
            color: #b91c1c;
        }
        @media (min-width: 1280px) { .inventory-show-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 767px) {
            .inventory-show-thumb-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .inventory-show-preview-image-wrap { min-height: 18rem; height: 18rem; }
            .inventory-show-preview-image-wrap img { max-height: 18rem; }
        }
    </style>
@endsection

@section('content')
    <div class="block justify-between page-header md:flex md:items-start gap-3">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">Inventory Item Details</h3>
        </div>
        <div class="flex flex-col items-start gap-3 md:items-end">
            <ol class="flex items-center whitespace-nowrap min-w-0">
                <li class="text-[0.813rem] ps-[0.5rem]">
                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.dashboard') }}">GSO<i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i></a>
                </li>
                <li class="text-[0.813rem] ps-[0.5rem]">
                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.inventory.index') }}">Inventory<i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i></a>
                </li>
                <li class="text-[0.813rem] ps-[0.5rem]">
                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.inventory-items.index') }}">Inventory Items<i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i></a>
                </li>
                <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">{{ $itemName }}</li>
            </ol>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-12 col-span-12">
            <div class="box">
                <div class="box-body">
                    <div class="grid grid-cols-12 gap-6">
                        <div class="xl:col-span-3 col-span-12">
                            <div class="inventory-show-gallery">
                                @if($photoFiles->isNotEmpty())
                                    @php
                                        $activePhoto = $photoFiles->first();
                                        $activePhotoUrl = $photoUrl($activePhoto);
                                        $activePhotoLabel = $activePhoto->original_name ?: (($inventoryItem->item?->item_name ?? 'Inventory photo') . ' 1');
                                    @endphp
                                    <div class="inventory-show-preview-frame">
                                        @if($photoFiles->count() > 1)
                                            <button type="button" class="inventory-show-gallery-nav" data-gallery-nav="prev" aria-label="Previous photo">
                                                <i class="ri-arrow-left-s-line text-[1.25rem]"></i>
                                            </button>
                                            <button type="button" class="inventory-show-gallery-nav" data-gallery-nav="next" aria-label="Next photo">
                                                <i class="ri-arrow-right-s-line text-[1.25rem]"></i>
                                            </button>
                                        @endif
                                        <div class="inventory-show-preview-image-wrap" id="inventoryShowPreviewWrap">
                                            <img
                                                id="inventoryShowActiveImage"
                                                src="{{ $activePhotoUrl }}"
                                                alt="{{ $activePhotoLabel }}"
                                            >
                                        </div>
                                    </div>

                                    <div class="inventory-show-thumb-strip" id="inventoryShowThumbStrip">
                                        @foreach($photoFiles as $index => $photo)
                                            @php
                                                $photoSrc = $photoUrl($photo);
                                                $photoLabel = $photo->original_name ?: (($inventoryItem->item?->item_name ?? 'Inventory photo') . ' ' . ($index + 1));
                                            @endphp
                                            <button
                                                type="button"
                                                class="inventory-show-thumb {{ $index === 0 ? 'is-active' : '' }}"
                                                data-gallery-thumb
                                                data-photo-index="{{ $index }}"
                                                data-photo-src="{{ $photoSrc }}"
                                                data-photo-alt="{{ $photoLabel }}"
                                                aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                                            >
                                                <img src="{{ $photoSrc }}" alt="{{ $photoLabel }}">
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="inventory-show-preview-frame">
                                        <div class="inventory-show-empty">
                                            <div>
                                                <i class="ri-image-line text-[2rem] text-[#8c9097] dark:text-white/50 mb-2 inline-block"></i>
                                                <p class="font-semibold text-defaulttextcolor dark:text-white">No inventory photos yet</p>
                                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1">Upload photos later to use this gallery.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="xl:col-span-9 col-span-12">
                            <div class="grid grid-cols-12 gap-6">
                                <div class="xl:col-span-8 col-span-12">
                                    <div class="mb-4">
                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                            <span class="badge bg-primary/10 text-primary">{{ $trackingLabel }}</span>
                                            @if(filled($inventoryItem->status))
                                                <span class="badge bg-success/10 text-success">Status: {{ $headline($inventoryItem->status) }}</span>
                                            @endif
                                            @if(filled($inventoryItem->condition))
                                                <span class="badge bg-info/10 text-info">Condition: {{ $headline($inventoryItem->condition) }}</span>
                                            @endif
                                            @if(filled($inventoryItem->custody_state))
                                                <span class="badge bg-warning/10 text-warning">Custody: {{ $headline($inventoryItem->custody_state) }}</span>
                                            @endif
                                            @if($inventoryItem->deleted_at)
                                                <span class="badge bg-danger/10 text-danger">Archived</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                                            <p class="text-[1.125rem] font-semibold mb-0">{{ $itemName }}</p>
                                            @if($canManageInventoryItems)
                                                <button
                                                    type="button"
                                                    id="btnOpenInventoryEditPrimary"
                                                    class="ti-btn ti-btn-light ti-btn-sm !px-2 !py-1"
                                                    data-hs-overlay="#inventoryEditRecordModal"
                                                    aria-label="Edit {{ $itemName }}"
                                                >
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-0">{{ $assetCategoryLabel !== '-' ? $assetCategoryLabel : 'Inventory asset record' }}</p>
                                    </div>

                                    <div class="inventory-show-summary mb-6">
                                        <div class="border border-defaultborder dark:border-defaultborder/10 rounded-md p-4 h-full">
                                            <p class="mb-1 text-[0.6875rem] text-success font-semibold">Acquisition</p>
                                            <p class="mb-1 text-[1.25rem] font-semibold">{{ $currency($inventoryItem->acquisition_cost) }}</p>
                                            <p class="mb-0 text-[#8c9097] dark:text-white/50 text-[0.75rem]">{{ $acquisitionDateLabel }}</p>
                                        </div>
                                        <div class="border border-defaultborder dark:border-defaultborder/10 rounded-md p-4 h-full">
                                            <p class="mb-1 text-[0.6875rem] text-info font-semibold">Assignment</p>
                                            <p class="mb-1 text-[1rem] font-semibold">{{ $departmentLabel }}</p>
                                            <p class="mb-0 text-[#8c9097] dark:text-white/50 text-[0.75rem]">{{ $accountableOfficerLabel }}</p>
                                        </div>
                                        <div class="border border-defaultborder dark:border-defaultborder/10 rounded-md p-4 h-full">
                                            <p class="mb-1 text-[0.6875rem] text-warning font-semibold">Reference Numbers</p>
                                            <p class="mb-1 text-[1rem] font-semibold">{{ $inventoryItem->property_number ?: '-' }}</p>
                                            <p class="mb-0 text-[#8c9097] dark:text-white/50 text-[0.75rem]">Stock: {{ $inventoryItem->stock_number ?: '-' }} | PO: {{ $inventoryItem->po_number ?: '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-[.9375rem] font-semibold mb-1">Description :</p>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-0">{{ $descriptionText }}</p>
                                    </div>

                                    <div class="mb-0">
                                        <p class="text-[.9375rem] font-semibold mb-2">Product Details :</p>
                                        <div class="table-responsive min-w-full">
                                            <table class="table table-bordered w-full">
                                                <tbody>
                                                    @foreach($detailRows as $row)
                                                        <tr class="border border-defaultborder dark:border-defaultborder/10">
                                                            <th scope="row" class="!font-semibold text-start w-[32%]">{{ $row['label'] }}</th>
                                                            <td class="text-[#8c9097] dark:text-white/50 whitespace-normal break-words">{{ $row['value'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box h-full">
                                        <div class="box-header">
                                            <div>
                                                <h5 class="box-title mb-1">History</h5>
                                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">{{ $historyEvents->count() }} record(s)</p>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="grid gap-2 mb-4">
                                                @if($canManageInventoryItems)
                                                    <button
                                                        type="button"
                                                        class="ti-btn ti-btn-primary w-full !justify-start"
                                                        data-hs-overlay="#inventoryEditRecordModal"
                                                    >
                                                        <i class="ri-edit-line me-1"></i> Edit Inventory Record
                                                    </button>
                                                @endif
                                                <a href="{{ $propertyCardUrl }}" target="_blank" rel="noopener" class="ti-btn ti-btn-light w-full !justify-start">
                                                    <i class="ri-file-list-3-line me-1"></i> View Property Card
                                                </a>
                                            </div>
                                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-4">Movement, accountability, and lifecycle notes for this asset.</p>
                                            @if($historyEvents->isEmpty())
                                                <div class="rounded-md border border-dashed border-defaultborder dark:border-defaultborder/10 p-4 text-[0.875rem] text-[#8c9097] dark:text-white/50">No history yet.</div>
                                            @else
                                                <div class="inventory-show-history space-y-3">
                                                    @foreach($historyEvents as $event)
                                                        @php
                                                            $label = InventoryEventTypes::labels()[$event->event_type] ?? Str::headline((string) ($event->event_type ?? 'Update'));
                                                            $qty = ($event->qty_in ?? 0) > 0 ? $event->qty_in : ($event->qty_out ?? 0);
                                                            $deptText = $event->department ? trim(collect([$event->department->code, $event->department->name])->filter()->implode(' - ')) : '-';
                                                            $referenceText = trim(collect([$event->reference_type, $event->reference_no])->filter()->implode(': '));
                                                            $referenceUrl = $referenceUrlFor($event->reference_type, $event->reference_id);
                                                        @endphp
                                                        <div class="rounded-md border border-defaultborder dark:border-defaultborder/10 p-4">
                                                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                                                <div class="text-[0.95rem] font-semibold text-defaulttextcolor dark:text-white">{{ $label }}</div>
                                                                <div class="text-[0.75rem] text-[#8c9097] dark:text-white/50">{{ $event->event_date?->format('M d, Y h:i A') ?: $event->created_at?->format('M d, Y h:i A') }}</div>
                                                            </div>
                                                            <div class="mt-3 text-[0.875rem] leading-7 text-defaulttextcolor dark:text-white">
                                                                <div><span class="font-semibold">Department:</span> {{ $deptText !== '' ? $deptText : '-' }}</div>
                                                                <div><span class="font-semibold">Accountable:</span> {{ $event->person_accountable ?: '-' }}</div>
                                                                <div><span class="font-semibold">Qty:</span> {{ $qty ?: 0 }}</div>
                                                                <div><span class="font-semibold">Property No:</span> {{ $inventoryItem->property_number ?: '-' }}</div>
                                                                @if($referenceText !== '')
                                                                    <div><span class="font-semibold">Reference:</span> @if($referenceUrl)<a href="{{ $referenceUrl }}" target="_blank" rel="noopener" class="text-primary hover:underline">{{ $referenceText }}</a>@else{{ $referenceText }}@endif</div>
                                                                @endif
                                                                @if(filled($event->notes))
                                                                    <div><span class="font-semibold">Remarks:</span> {{ $event->notes }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-12 gap-6 mt-6">
                                <div class="xl:col-span-6 col-span-12">
                                    <div class="box h-full">
                                        <div class="box-body">
                                            <div class="mb-3">
                                                <p class="text-[.9375rem] font-semibold mb-1">Remarks</p>
                                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Internal notes captured for this asset record.</p>
                                            </div>
                                            @if($remarksText !== '')
                                                <p class="text-[#8c9097] dark:text-white/50 leading-6 whitespace-pre-line mb-0">{{ $remarksText }}</p>
                                            @else
                                                <div class="rounded-md border border-dashed border-defaultborder dark:border-defaultborder/10 p-4 text-[0.8125rem] text-[#8c9097] dark:text-white/50">No remarks recorded for this asset yet.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-6 col-span-12">
                                    <div class="box h-full">
                                        <div class="box-body">
                                            <div class="mb-3">
                                                <p class="text-[.9375rem] font-semibold mb-1">Linked Records</p>
                                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Related AIR and workflow references.</p>
                                            </div>
                                            <div class="grid gap-2">
                                                @forelse($linkedRecords as $record)
                                                    <div>
                                                        @if(filled($record['url'] ?? null))
                                                            <a href="{{ $record['url'] }}" target="_blank" rel="noopener" class="ti-btn ti-btn-light !justify-center w-full">{{ $record['label'] }}</a>
                                                        @else
                                                            <div class="rounded-md border border-defaultborder dark:border-defaultborder/10 px-4 py-3 text-center text-defaulttextcolor dark:text-white">{{ $record['label'] }}</div>
                                                        @endif
                                                        @if(filled($record['meta'] ?? null))
                                                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1 mb-0 px-1">{{ $record['meta'] }}</p>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="rounded-md border border-dashed border-defaultborder dark:border-defaultborder/10 p-4 text-[0.8125rem] text-[#8c9097] dark:text-white/50">No linked AIR or workflow record for this asset yet.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($canManageInventoryItems)
        @include('gso::inventory-items.partials.edit-modal')
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileConfig = {
                storeUrl: @json(route('gso.inventory-items.files.store', ['inventoryItem' => $inventoryItem->id])),
                destroyUrlTemplate: @json(route('gso.inventory-items.files.destroy', ['inventoryItem' => $inventoryItem->id, 'file' => '__FILE__'])),
                csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',
            };

            const previewWrap = document.getElementById('inventoryShowPreviewWrap');
            const thumbStrip = document.getElementById('inventoryShowThumbStrip');
            const activeImage = document.getElementById('inventoryShowActiveImage');
            const previousButton = document.querySelector('[data-gallery-nav="prev"]');
            const nextButton = document.querySelector('[data-gallery-nav="next"]');
            const photoNotice = document.getElementById('inventoryEditPhotoNotice');
            const photoInput = document.getElementById('inventoryEditPhotoFiles');
            const photoUploadButton = document.getElementById('inventoryEditPhotoUploadBtn');
            const photoGrid = document.getElementById('inventoryEditPhotoGrid');

            let galleryFiles = @json($galleryPhotoFiles);
            let currentIndex = 0;

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const showPhotoNotice = (message, tone = 'success') => {
                if (!photoNotice) {
                    return;
                }

                photoNotice.textContent = message;
                photoNotice.className = `mb-3 rounded-md px-3 py-2 text-sm inventory-edit-photo-notice-${tone}`;
                photoNotice.classList.remove('hidden');
            };

            const clearPhotoNotice = () => {
                if (!photoNotice) {
                    return;
                }

                photoNotice.textContent = '';
                photoNotice.className = 'hidden mb-3 rounded-md px-3 py-2 text-sm';
            };

            const setGalleryIndex = (index) => {
                if (!previewWrap || !thumbStrip || !Array.isArray(galleryFiles) || galleryFiles.length === 0) {
                    return;
                }

                currentIndex = Math.max(0, Math.min(index, galleryFiles.length - 1));
                const file = galleryFiles[currentIndex];

                previewWrap.innerHTML = `
                    <img
                        id="inventoryShowActiveImage"
                        src="${escapeHtml(file.url)}"
                        alt="${escapeHtml(file.name)}"
                    >
                `;

                const thumbs = Array.from(thumbStrip.querySelectorAll('[data-gallery-thumb]'));
                thumbs.forEach((thumb, thumbIndex) => {
                    const isActive = thumbIndex === currentIndex;
                    thumb.classList.toggle('is-active', isActive);
                    thumb.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const renderGallery = (files) => {
                galleryFiles = Array.isArray(files) ? files : [];

                if (!previewWrap || !thumbStrip) {
                    return;
                }

                if (galleryFiles.length === 0) {
                    previewWrap.innerHTML = `
                        <div class="inventory-show-empty">
                            <div>
                                <i class="ri-image-line text-[2rem] text-[#8c9097] dark:text-white/50 mb-2 inline-block"></i>
                                <p class="font-semibold text-defaulttextcolor dark:text-white">No inventory photos yet</p>
                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1">Upload photos later to use this gallery.</p>
                            </div>
                        </div>
                    `;
                    thumbStrip.innerHTML = '';
                    previousButton?.classList.add('hidden');
                    nextButton?.classList.add('hidden');
                    return;
                }

                thumbStrip.innerHTML = galleryFiles.map((file, index) => `
                    <button
                        type="button"
                        class="inventory-show-thumb ${index === currentIndex ? 'is-active' : ''}"
                        data-gallery-thumb
                        data-photo-index="${index}"
                        aria-pressed="${index === currentIndex ? 'true' : 'false'}"
                    >
                        <img src="${escapeHtml(file.url)}" alt="${escapeHtml(file.name)}">
                    </button>
                `).join('');

                Array.from(thumbStrip.querySelectorAll('[data-gallery-thumb]')).forEach((thumb) => {
                    thumb.addEventListener('click', () => {
                        const index = Number(thumb.getAttribute('data-photo-index') || 0);
                        setGalleryIndex(index);
                    });
                });

                if (galleryFiles.length > 1) {
                    previousButton?.classList.remove('hidden');
                    nextButton?.classList.remove('hidden');
                } else {
                    previousButton?.classList.add('hidden');
                    nextButton?.classList.add('hidden');
                }

                setGalleryIndex(Math.min(currentIndex, galleryFiles.length - 1));
            };

            const renderModalPhotos = (files) => {
                if (!photoGrid) {
                    return;
                }

                const photoFiles = (Array.isArray(files) ? files : []).filter((file) => file && file.url);

                if (photoFiles.length === 0) {
                    photoGrid.innerHTML = `
                        <div class="inventory-edit-photo-empty md:col-span-3">
                            No images uploaded yet.
                        </div>
                    `;
                    return;
                }

                photoGrid.innerHTML = photoFiles.map((file) => `
                    <div class="inventory-edit-photo-card">
                        <img src="${escapeHtml(file.url)}" alt="${escapeHtml(file.name)}" class="inventory-edit-photo-card-image">
                        <div class="inventory-edit-photo-card-body">
                            <div class="flex items-start justify-between gap-2">
                                <div class="inventory-edit-photo-card-name">${escapeHtml(file.name)}</div>
                                ${file.is_primary ? '<span class="inventory-edit-photo-badge">Primary</span>' : ''}
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="button" class="ti-btn ti-btn-sm ti-btn-danger" data-delete-photo-id="${escapeHtml(file.id)}">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');

                Array.from(photoGrid.querySelectorAll('[data-delete-photo-id]')).forEach((button) => {
                    button.addEventListener('click', async () => {
                        const fileId = button.getAttribute('data-delete-photo-id');
                        if (!fileId || !confirm('Remove this image from the inventory record?')) {
                            return;
                        }

                        clearPhotoNotice();
                        button.disabled = true;

                        try {
                            const response = await fetch(
                                fileConfig.destroyUrlTemplate.replace('__FILE__', encodeURIComponent(fileId)),
                                {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': fileConfig.csrf,
                                        'Accept': 'application/json',
                                    },
                                }
                            );

                            const payload = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                throw new Error(payload?.message || 'Unable to remove the image.');
                            }

                            const files = (payload?.data?.files || [])
                                .filter((file) => file && (file.is_image || file.type === 'photo'))
                                .map((file) => ({
                                    id: file.id,
                                    url: file.preview_url,
                                    name: file.original_name || file.caption || 'Inventory photo',
                                    is_primary: !!file.is_primary,
                                }));

                            renderModalPhotos(files);
                            renderGallery(files);
                            showPhotoNotice('Image removed.');
                        } catch (error) {
                            showPhotoNotice(error instanceof Error ? error.message : 'Unable to remove the image.', 'error');
                        } finally {
                            button.disabled = false;
                        }
                    });
                });
            };

            previousButton?.addEventListener('click', () => {
                if (!galleryFiles.length) {
                    return;
                }

                setGalleryIndex((currentIndex - 1 + galleryFiles.length) % galleryFiles.length);
            });

            nextButton?.addEventListener('click', () => {
                if (!galleryFiles.length) {
                    return;
                }

                setGalleryIndex((currentIndex + 1) % galleryFiles.length);
            });

            photoUploadButton?.addEventListener('click', async () => {
                const files = Array.from(photoInput?.files || []);
                if (files.length === 0) {
                    showPhotoNotice('Choose one or more images first.', 'error');
                    return;
                }

                clearPhotoNotice();
                photoUploadButton.disabled = true;

                const formData = new FormData();
                files.forEach((file) => formData.append('files[]', file));
                formData.append('type', 'photo');

                try {
                    const response = await fetch(fileConfig.storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': fileConfig.csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(payload?.message || 'Unable to upload the images.');
                    }

                    const files = (payload?.data?.files || [])
                        .filter((file) => file && (file.is_image || file.type === 'photo'))
                        .map((file) => ({
                            id: file.id,
                            url: file.preview_url,
                            name: file.original_name || file.caption || 'Inventory photo',
                            is_primary: !!file.is_primary,
                        }));

                    if (photoInput) {
                        photoInput.value = '';
                    }

                    renderModalPhotos(files);
                    renderGallery(files);
                    showPhotoNotice('Images uploaded.');
                } catch (error) {
                    showPhotoNotice(error instanceof Error ? error.message : 'Unable to upload the images.', 'error');
                } finally {
                    photoUploadButton.disabled = false;
                }
            });

            renderGallery(galleryFiles);
            renderModalPhotos(galleryFiles);
        });
    </script>
    @if(isset($errors) && $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelector('[data-hs-overlay="#inventoryEditRecordModal"]')?.click();
            });
        </script>
    @endif
@endpush
