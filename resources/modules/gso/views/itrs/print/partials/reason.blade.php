@php
    $document = $report['document'] ?? [];
    $reason = trim((string) ($document['reason_for_transfer'] ?? ''));
    $reasonLines = preg_split('/\r\n|\r|\n/', $reason) ?: [];
    $reasonLines = array_values(array_filter(array_map(fn ($line) => trim((string) $line), $reasonLines), fn ($line) => $line !== ''));
@endphp

<table class="gso-ptr-print-reason gso-ptr-print-stack-next">
    <tbody>
        <tr>
            <td>
                <div class="gso-ptr-print-reason-body">
                    <div class="gso-ptr-print-meta-label">Reason for Transfer:</div>
                    <div class="gso-ptr-print-reason-lines">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="gso-ptr-print-reason-line">{{ $reasonLines[$i] ?? '' }}</div>
                        @endfor
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
