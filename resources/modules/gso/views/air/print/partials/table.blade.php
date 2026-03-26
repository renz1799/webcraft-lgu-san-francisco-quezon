@php
    $document = $report['document'] ?? [];
    $rows = collect($rows ?? [])->values();
    $gridRows = max((int) ($gridRows ?? 24), $rows->count());
    $fillRows = (bool) ($fillRows ?? true);
    $isLastPage = (int) ($pageIndex ?? 0) + 1 === (int) ($totalPages ?? 1);
    $lastPageGridRows = max(0, (int) ($lastPageGridRows ?? 0));
    $remainingRows = $fillRows ? max(0, $gridRows - $rows->count()) : 0;

    if ($isLastPage && $lastPageGridRows > 0) {
        $remainingRows = max(0, $lastPageGridRows - $rows->count());
    }

    $receivedCompleteness = strtolower((string) ($document['received_completeness'] ?? ''));
    $dateReceived = ($document['date_received_label'] ?? '') ?: '';
    $dateInspected = ($document['date_inspected_label'] ?? '') ?: '';
    $acceptedByName = ($document['accepted_by_name'] ?? '') ?: ' ';
    $inspectedByName = ($document['inspected_by_name'] ?? '') ?: ' ';
@endphp

<table class="gso-air-print-sheet">
    <colgroup>
        <col style="width:18%;">
        <col style="width:52%;">
        <col style="width:15%;">
        <col style="width:15%;">
    </colgroup>
    <tbody>
        @include('gso::air.print.partials.meta', [
            'report' => $report,
        ])

        <tr class="gso-air-print-column-head">
            <th>Property No.</th>
            <th>Description</th>
            <th>Unit</th>
            <th>Quantity</th>
        </tr>

        @if ($rows->isEmpty())
            <tr>
                <td colspan="4" class="gso-air-print-empty-note">No AIR line items are available for print yet.</td>
            </tr>
            @php $remainingRows = max(0, $remainingRows - 1); @endphp
        @else
            @foreach ($rows as $row)
                <tr>
                    <td class="gso-air-print-center">{{ $row['property_no'] ?: ' ' }}</td>
                    <td>{{ $row['description'] ?: ' ' }}</td>
                    <td class="gso-air-print-center">{{ $row['unit'] ?: ' ' }}</td>
                    <td class="gso-air-print-center">{{ $row['quantity'] !== '' ? $row['quantity'] : ' ' }}</td>
                </tr>
            @endforeach
        @endif

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
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
