@php
    use App\Modules\GSO\Support\InventoryConditions;
    use App\Modules\GSO\Support\InventoryStatuses;

    $currentStatus = old('status', $inventoryItem->status);
    $currentCondition = old('condition', $inventoryItem->condition);
@endphp

<div id="inventoryEditRecordModal"
     class="hs-overlay hidden ti-modal [--overlay-backdrop:static]"
     role="dialog"
     tabindex="-1">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-5xl">
        <div class="ti-modal-content">
            <div class="ti-modal-header">
                <div>
                    <h6 class="modal-title text-[1rem] font-semibold">Edit Inventory Record</h6>
                    <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1 mb-0">Update the stored details for {{ $itemName }} without leaving this page.</p>
                </div>

                <button type="button"
                        class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor"
                        data-hs-overlay="#inventoryEditRecordModal">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <form id="inventoryEditRecordForm"
                  method="POST"
                  action="{{ route('gso.inventory-items.update', $inventoryItem->id) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="ti-modal-body px-4">
                    <div class="space-y-5 max-h-[75vh] overflow-y-auto pe-1">
                        <div class="grid grid-cols-12 gap-4">
                        <div class="md:col-span-6 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Item</label>
                            <input type="text"
                                   class="ti-form-input w-full"
                                   value="{{ $inventoryItem->item?->item_name ?? $inventoryItem->item_name ?? '-' }}"
                                   disabled>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1">Item identity stays linked to the source item record.</p>
                        </div>

                        <div class="md:col-span-6 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">PO Number</label>
                            <input type="text"
                                   class="ti-form-input w-full"
                                   value="{{ $inventoryItem->po_number ?? '-' }}"
                                   disabled>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1">Purchase order is read-only on the inventory record.</p>
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Property Number</label>
                            <input type="text" name="property_number" class="ti-form-input w-full" value="{{ old('property_number', $inventoryItem->property_number) }}">
                            @error('property_number')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Stock Number</label>
                            <input type="text" name="stock_number" class="ti-form-input w-full" value="{{ old('stock_number', $inventoryItem->stock_number) }}">
                            @error('stock_number')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Service Life (years)</label>
                            <input type="number" min="0" name="service_life" class="ti-form-input w-full" value="{{ old('service_life', $inventoryItem->service_life) }}">
                            @error('service_life')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-6 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Department</label>
                            <select name="department_id" class="ti-form-select w-full">
                                <option value="">- Select Department -</option>
                                @foreach(($departments ?? collect()) as $dept)
                                    <option value="{{ $dept->id }}" @selected((string) old('department_id', $inventoryItem->department_id) === (string) $dept->id)>
                                        {{ $dept->code }} - {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-6 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Fund Source</label>
                            <select name="fund_source_id" class="ti-form-select w-full">
                                <option value="">- Select Fund Source -</option>
                                @foreach(($fundSources ?? collect()) as $fund)
                                    <option value="{{ $fund->id }}" @selected((string) old('fund_source_id', $inventoryItem->fund_source_id) === (string) $fund->id)>
                                        {{ $fund->code ? ($fund->code . ' - ') : '' }}{{ $fund->name ?? 'Fund' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fund_source_id')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Acquisition Date</label>
                            <input type="date" name="acquisition_date" class="ti-form-input w-full" value="{{ old('acquisition_date', optional($inventoryItem->acquisition_date)->format('Y-m-d')) }}" required>
                            @error('acquisition_date')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Acquisition Cost</label>
                            <input type="text" name="acquisition_cost" class="ti-form-input w-full" value="{{ old('acquisition_cost', $inventoryItem->acquisition_cost) }}" required>
                            @error('acquisition_cost')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Quantity</label>
                            <input type="number" min="1" name="quantity" class="ti-form-input w-full" value="{{ old('quantity', $inventoryItem->quantity ?? 1) }}" required>
                            @error('quantity')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-2 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Unit</label>
                            <input type="text" name="unit" class="ti-form-input w-full" value="{{ old('unit', $inventoryItem->unit) }}">
                            @error('unit')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-6 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Status</label>
                            <select name="status" class="ti-form-select w-full" required>
                                <option value="">- Select Status -</option>
                                @foreach(InventoryStatuses::labels() as $value => $label)
                                    <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-6 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Condition</label>
                            <select name="condition" class="ti-form-select w-full" required>
                                <option value="">- Select Condition -</option>
                                @foreach(InventoryConditions::labels() as $value => $label)
                                    <option value="{{ $value }}" @selected($currentCondition === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('condition')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-12 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Accountable Officer</label>
                            <input type="text" name="accountable_officer" class="ti-form-input w-full" value="{{ old('accountable_officer', $inventoryItem->accountable_officer) }}">
                            @error('accountable_officer')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Brand</label>
                            <input type="text" name="brand" class="ti-form-input w-full" value="{{ old('brand', $inventoryItem->brand) }}">
                            @error('brand')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Model</label>
                            <input type="text" name="model" class="ti-form-input w-full" value="{{ old('model', $inventoryItem->model) }}">
                            @error('model')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-4 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Serial Number</label>
                            <input type="text" name="serial_number" class="ti-form-input w-full" value="{{ old('serial_number', $inventoryItem->serial_number) }}">
                            @error('serial_number')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-12 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Description</label>
                            <textarea name="description" rows="4" class="ti-form-input w-full">{{ old('description', $inventoryItem->description) }}</textarea>
                            @error('description')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="md:col-span-12 col-span-12">
                            <label class="text-sm text-[#8c9097] dark:text-white/50">Remarks</label>
                            <textarea name="remarks" rows="3" class="ti-form-input w-full">{{ old('remarks', $inventoryItem->remarks) }}</textarea>
                            @error('remarks')<div class="text-xs text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="border-t border-defaultborder pt-5">
                        <div class="mb-3">
                            <h6 class="text-sm font-semibold text-defaulttextcolor dark:text-white mb-1">Record Images</h6>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">Manage the inventory photos shown on the item page. Uploads are saved immediately.</p>
                        </div>

                        <div id="inventoryEditPhotoNotice" class="hidden mb-3 rounded-md px-3 py-2 text-sm"></div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <input
                                id="inventoryEditPhotoFiles"
                                type="file"
                                class="ti-form-input w-full"
                                multiple
                                accept="image/*"
                            >
                            <button type="button" id="inventoryEditPhotoUploadBtn" class="ti-btn ti-btn-primary shrink-0">
                                Upload Images
                            </button>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-3" id="inventoryEditPhotoGrid"></div>
                    </div>
                </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#inventoryEditRecordModal">Cancel</button>
                    <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
