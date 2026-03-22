<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Property Card - {{ $card['reference'] ?? ($card['property_name'] ?? 'Property Card') }}</title>
  @include('gso::property-cards.partials.pc-print-styles')
</head>

<body>
@php
  $isPreview = (bool) ($isPreview ?? request()->boolean('preview'));
@endphp

@include('gso::property-cards.partials.pc-card', [
  'card' => $card ?? [],
  'entries' => $entries ?? [],
  'maxGridRows' => $maxGridRows ?? 18,
  'isPreview' => $isPreview,
])

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
