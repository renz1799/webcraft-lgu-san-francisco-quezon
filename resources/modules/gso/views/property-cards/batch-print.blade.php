<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Property Cards Batch Print</title>
  @include('gso::property-cards.partials.pc-print-styles')
  @include('gso::property-cards.partials.ics-print-styles')
  <style>
    .batch-card { page-break-after: always; }
    .batch-card:last-child { page-break-after: auto; }
    .batch-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin: 10px 0;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      color: #475569;
    }

    .batch-toolbar button,
    .batch-toolbar a {
      border: 1px solid #cbd5e1;
      background: #fff;
      border-radius: 8px;
      padding: 8px 12px;
      color: #0f172a;
      text-decoration: none;
      cursor: pointer;
    }

    @media print {
      .batch-toolbar { display: none !important; }
    }
  </style>
</head>
<body>
@php
  $isPreview = (bool) ($isPreview ?? request()->boolean('preview'));
  $cards = $cards ?? [];
@endphp

<div class="batch-toolbar">
  <div>
    Printing page {{ (int) ($page ?? 1) }} with {{ count($cards) }} card(s), Total matching: {{ (int) ($total ?? 0) }}
  </div>
  <div style="display:flex; gap:8px;">
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ route('gso.inventory-items.index') }}" target="_blank" rel="noopener">Inventory Items</a>
  </div>
</div>

@foreach($cards as $cardPayload)
  <div class="batch-card">
    @if(($cardPayload['view'] ?? '') === 'gso::property-cards.ics-print')
      @include('gso::property-cards.partials.ics-card', [
        'card' => $cardPayload['data']['card'] ?? [],
        'entries' => $cardPayload['data']['entries'] ?? [],
        'maxGridRows' => $cardPayload['data']['maxGridRows'] ?? 18,
        'isPreview' => true,
      ])
    @else
      @include('gso::property-cards.partials.pc-card', [
        'card' => $cardPayload['data']['card'] ?? [],
        'entries' => $cardPayload['data']['entries'] ?? [],
        'maxGridRows' => $cardPayload['data']['maxGridRows'] ?? 18,
        'isPreview' => true,
      ])
    @endif
  </div>
@endforeach

<script>
  window.addEventListener('load', () => {
    const isPreview = {{ $isPreview ? 'true' : 'false' }};
    if (!isPreview) {
      window.print();
    }
  });
</script>
</body>
</html>
