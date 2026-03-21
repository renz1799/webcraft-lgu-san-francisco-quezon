@props([
  'kicker' => null,
  'title' => null,
  'copy' => null,
])

<section class="print-workspace-panel">
  @if($kicker || $title || $copy)
    <div class="print-workspace-panel-head">
      @if($kicker)
        <div class="print-workspace-kicker">{{ $kicker }}</div>
      @endif

      @if($title)
        <h1 class="print-workspace-title">{{ $title }}</h1>
      @endif

      @if($copy)
        <p class="print-workspace-copy">{{ $copy }}</p>
      @endif
    </div>
  @endif

  {{ $slot }}
</section>
