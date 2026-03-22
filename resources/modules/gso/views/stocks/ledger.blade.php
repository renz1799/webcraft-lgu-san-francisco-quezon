@extends('layouts.master')

@php
    $cardParams = ['item' => $item->id, 'preview' => 1];
    if (!empty($filters['fund_source_id'])) {
        $cardParams['fund_source_id'] = $filters['fund_source_id'];
    }
@endphp

@section('content')
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
            Stock Ledger
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            {{ $item->item_name }}{{ $item->item_identification ? ' | Stock No: ' . $item->item_identification : '' }}{{ $item->base_unit ? ' | Unit: ' . $item->base_unit : '' }}
        </p>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('gso.stocks.card.print', $cardParams) }}" target="_blank" rel="noopener" class="ti-btn ti-btn-primary">
            Stock Card
        </a>
        <a href="{{ route('gso.stocks.index') }}" class="ti-btn ti-btn-light">Back to Stocks</a>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="flex items-center justify-between gap-3 flex-wrap w-full">
            <div>
                <h5 class="box-title">Movement Ledger</h5>
                <p class="text-xs text-[#8c9097] mt-1 mb-0">Current on hand: {{ number_format((int) $onHand) }}</p>
            </div>
        </div>
    </div>

    <div class="box-body">
        <form method="GET" action="{{ route('gso.stocks.ledger', ['item' => $item->id]) }}" class="grid grid-cols-1 lg:grid-cols-5 gap-3 mb-4">
            <input name="date_from" type="date" class="ti-form-input w-full" value="{{ $filters['date_from'] ?? '' }}">
            <input name="date_to" type="date" class="ti-form-input w-full" value="{{ $filters['date_to'] ?? '' }}">

            <select name="fund_source_id" class="ti-form-select w-full">
                <option value="">All Fund Sources</option>
                @foreach($availableFunds as $fund)
                    <option value="{{ $fund['id'] }}" @selected((string) ($filters['fund_source_id'] ?? '') === (string) ($fund['id'] ?? ''))>
                        {{ $fund['label'] }} ({{ $fund['on_hand'] }})
                    </option>
                @endforeach
            </select>

            <select name="type" class="ti-form-select w-full">
                <option value="">All Movement Types</option>
                <option value="in" @selected(($filters['type'] ?? '') === 'in')>Receipt</option>
                <option value="issue" @selected(($filters['type'] ?? '') === 'issue')>Issue</option>
                <option value="restore" @selected(($filters['type'] ?? '') === 'restore')>Restore</option>
                <option value="adjust" @selected(($filters['type'] ?? '') === 'adjust')>Adjustments</option>
                <option value="adjust_in" @selected(($filters['type'] ?? '') === 'adjust_in')>Adjust In</option>
                <option value="adjust_out" @selected(($filters['type'] ?? '') === 'adjust_out')>Adjust Out</option>
                <option value="adjust_set" @selected(($filters['type'] ?? '') === 'adjust_set')>Adjust Set</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="ti-btn ti-btn-primary flex-1">Apply</button>
                <a href="{{ route('gso.stocks.ledger', ['item' => $item->id]) }}" class="ti-btn ti-btn-light">Reset</a>
            </div>
        </form>

        <div class="overflow-auto table-bordered">
            <table class="ti-custom-table ti-striped-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">Date</th>
                        <th class="text-left">Fund Source</th>
                        <th class="text-left">Type</th>
                        <th class="text-right">Qty</th>
                        <th class="text-left">Reference</th>
                        <th class="text-left">Remarks</th>
                        <th class="text-left">By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->occurred_at?->format('M j, Y g:i A') ?? '-' }}</td>
                            <td>
                                @if($movement->fundSource)
                                    {{ $movement->fundSource->code }} - {{ $movement->fundSource->name }}
                                @else
                                    Unassigned
                                @endif
                            </td>
                            <td>{{ \App\Modules\GSO\Support\StockMovementTypes::label($movement->movement_type) }}</td>
                            <td class="text-right">{{ number_format((int) ($movement->qty ?? 0)) }}</td>
                            <td>
                                @php
                                    $referenceType = trim((string) ($movement->reference_type ?? ''));
                                    $referenceId = trim((string) ($movement->reference_id ?? ''));
                                @endphp
                                @if($referenceType !== '' || $referenceId !== '')
                                    {{ $referenceType !== '' ? $referenceType : 'Reference' }}{{ $referenceId !== '' ? ': ' . $referenceId : '' }}
                                @else
                                    Manual Adjustment
                                @endif
                            </td>
                            <td>{{ $movement->remarks ?: '-' }}</td>
                            <td>{{ $movement->created_by_name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-[#8c9097] py-6">No stock movements found for the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {!! $movements->withQueryString()->links() !!}
        </div>
    </div>
</div>
@endsection
