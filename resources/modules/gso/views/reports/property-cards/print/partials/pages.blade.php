@php
    $cards = collect($report['cards'] ?? []);
@endphp

@if ($cards->isEmpty())
    @include('gso::reports.property-cards.print.partials.empty-page', [
        'report' => $report,
        'paperProfile' => $paperProfile,
    ])
@else
    @foreach ($cards as $card)
        @include(
            ($card['type'] ?? 'pc') === 'ics'
                ? 'gso::reports.property-cards.print.partials.ics-card'
                : 'gso::reports.property-cards.print.partials.pc-card',
            [
                'paperProfile' => $paperProfile,
                'card' => $card['card'] ?? [],
                'entries' => $card['entries'] ?? [],
                'maxGridRows' => $card['maxGridRows'] ?? 18,
            ]
        )
    @endforeach
@endif
