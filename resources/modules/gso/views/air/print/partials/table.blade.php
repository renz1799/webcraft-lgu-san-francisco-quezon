@php
    $document = $report['document'] ?? [];
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 24), $rows->count());
    $fillRows = (bool) ($fillRows ?? true);
    $isLastPage = (int) ($pageIndex ?? 0) + 1 === (int) ($totalPages ?? 1);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $usedGridUnits = max($rows->count(), (int) ($usedGridUnits ?? $rows->count()));
    $remainingRows = $fillRows ? max(0, $gridRows - $usedGridUnits) : 0;

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $usedGridUnits);
    }

    $receivedCompleteness = strtolower((string) ($document['received_completeness'] ?? ''));
    $dateReceived = ($document['date_received_label'] ?? '') ?: '';
    $dateInspected = ($document['date_inspected_label'] ?? '') ?: '';
    $acceptedByName = ($document['accepted_by_name'] ?? '') ?: ' ';
    $inspectedByName = ($document['inspected_by_name'] ?? '') ?: ' ';
@endphp

<table class="gso-air-print-sheet">
    <colgroup>
        <col style="width:16%;">
        <col style="width:54%;">
        <col style="width:12%;">
        <col style="width:18%;">
    </colgroup>
    <tbody>
        @include('gso::air.print.partials.meta', [
            'report' => $report,
        ])

        <tr class="gso-air-print-column-head">
            <th class="gso-air-print-col--property" style="width:16%;">Property No.</th>
            <th class="gso-air-print-col--description" style="width:54%;">Description</th>
            <th class="gso-air-print-col--unit gso-air-print-col-head--compact" style="width:12%;">Unit</th>
            <th class="gso-air-print-col--quantity gso-air-print-col-head--compact" style="width:18%;">Quantity</th>
        </tr>

        @if ($rows->isEmpty())
            <tr>
                <td colspan="4" class="gso-air-print-empty-note">No AIR line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr>
                    <td class="gso-air-print-center gso-air-print-col--property" style="width:16%;">{{ $row['property_no'] ?: ' ' }}</td>
                    <td class="gso-air-print-col--description" style="width:54%;">{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-air-print-center gso-air-print-cell--compact gso-air-print-col--unit gso-air-print-col-cell--compact" style="width:12%;">{{ $row['unit'] ?: ' ' }}</td>
                    <td class="gso-air-print-center gso-air-print-cell--compact gso-air-print-cell--numeric gso-air-print-col--quantity gso-air-print-col-cell--compact" style="width:18%;">{{ $row['quantity'] !== '' ? $row['quantity'] : ' ' }}</td>
                </tr>
            @endforeach
        @endif

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr>
                <td class="gso-air-print-col--property" style="width:16%;">&nbsp;</td>
                <td class="gso-air-print-col--description" style="width:54%;">&nbsp;</td>
                <td class="gso-air-print-col--unit" style="width:12%;">&nbsp;</td>
                <td class="gso-air-print-col--quantity" style="width:18%;">&nbsp;</td>
            </tr>
        @endfor

        @if ($isLastPage)
            <tr>
                <td colspan="4" class="gso-air-print-acceptance-wrap">
                    <table class="gso-air-print-signatures">
                        <colgroup>
                            <col style="width:50%;">
                            <col style="width:50%;">
                        </colgroup>
                        <tr class="gso-air-print-acceptance-head">
                            <th>Acceptance</th>
                            <th>Inspection</th>
                        </tr>
                        <tr class="gso-air-print-acceptance-body">
                            <td class="gso-air-print-acceptance-cell">
                                <div class="gso-air-print-date-field">
                                    <span class="gso-air-print-date-label">Date Received :</span>
                                    <span class="gso-air-print-date-line">{{ $dateReceived }}</span>
                                </div>
                                <div class="gso-air-print-choice-row">
                                    <span class="gso-air-print-checkbox">{{ $receivedCompleteness === 'complete' ? 'X' : '' }}</span>
                                    <span>Complete</span>
                                </div>
                                <div class="gso-air-print-choice-row">
                                    <span class="gso-air-print-checkbox">{{ $receivedCompleteness === 'partial' ? 'X' : '' }}</span>
                                    <span>
                                        Partial
                                        @if (!empty($document['received_notes'] ?? null))
                                            ({{ $document['received_notes'] }})
                                        @else
                                            (please specify)
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="gso-air-print-acceptance-cell">
                                <div class="gso-air-print-date-field">
                                    <span class="gso-air-print-date-label">Date Inspected :</span>
                                    <span class="gso-air-print-date-line">{{ $dateInspected }}</span>
                                </div>
                                <div class="gso-air-print-choice-row">
                                    <span class="gso-air-print-checkbox">{{ !empty($document['inspection_verified'] ?? null) ? 'X' : '' }}</span>
                                    <span>Inspected, verified and found in order as to quantity and specifications.</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="gso-air-print-signoff-row">
                            <td class="gso-air-print-signoff-cell">
                                <div class="gso-air-print-signature-name">{{ $acceptedByName }}</div>
                                <div class="gso-air-print-signature-role">{{ $document['accepted_by_designation'] ?? '' }}</div>
                            </td>
                            <td class="gso-air-print-signoff-cell">
                                <div class="gso-air-print-signature-name">{{ $inspectedByName }}</div>
                                <div class="gso-air-print-signature-role">{{ $document['inspected_by_designation'] ?? '' }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif
    </tbody>
</table>
