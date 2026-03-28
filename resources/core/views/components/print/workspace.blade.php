@props([
  'sidebarWidth' => 'clamp(320px, calc(210mm * 0.44), 390px)',
  'previewWidth' => '210mm',
  'gap' => '28px',
])

<div
  class="print-workspace"
  data-print-workspace="1"
  style="--print-workspace-sidebar-width: {{ $sidebarWidth }}; --print-workspace-preview-width: {{ $previewWidth }}; --print-workspace-gap: {{ $gap }};"
>
  <aside class="print-workspace-sidebar" data-print-workspace-sidebar="1">
    {{ $sidebar ?? '' }}
  </aside>

  <main class="print-workspace-preview" data-print-workspace-preview="1">
    {{ $slot }}
  </main>
</div>
