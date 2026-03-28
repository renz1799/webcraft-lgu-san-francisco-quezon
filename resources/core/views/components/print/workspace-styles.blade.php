<style>
  .print-workspace-body {
    margin: 0;
    background: linear-gradient(180deg, #edf2f8 0%, #e6edf5 100%);
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: #0f172a;
  }

  .print-workspace-body--embedded {
    background: transparent;
  }

  .print-workspace-body--embedded .print-workspace {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding-inline: 0;
    justify-content: start;
  }

  .print-workspace-body--embedded .print-workspace-panel {
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
  }

  .print-workspace {
    width: max-content;
    margin: 0 auto;
    padding: 24px;
    display: grid;
    grid-template-columns:
      var(--print-workspace-sidebar-width, clamp(320px, calc(210mm * 0.44), 390px))
      var(--print-workspace-preview-width, 210mm);
    justify-content: center;
    align-items: start;
    gap: var(--print-workspace-gap, 28px);
  }

  .print-workspace-sidebar {
    width: var(--print-workspace-sidebar-width, clamp(320px, calc(210mm * 0.44), 390px));
    position: sticky;
    top: 24px;
    z-index: 3;
  }

  .print-workspace-preview {
    width: var(--print-workspace-preview-width, 210mm);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    position: relative;
    z-index: 1;
  }

  .print-workspace.is-preview-loading .print-workspace-preview {
    opacity: 0.68;
    transition: opacity 0.18s ease;
  }

  .print-workspace.is-preview-loading .print-workspace-sidebar {
    pointer-events: none;
  }

  .print-workspace-panel {
    background: #fff;
    border: 1px solid #d9e2ee;
    border-radius: 22px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
    padding: 22px;
  }

  .print-workspace-panel-head {
    margin-bottom: 18px;
  }

  .print-workspace-kicker {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9a3412;
    margin-bottom: 8px;
  }

  .print-workspace-title {
    margin: 0;
    font-size: 24px;
    line-height: 1.1;
    color: #111827;
  }

  .print-workspace-copy {
    margin: 10px 0 0;
    font-size: 13px;
    line-height: 1.55;
    color: #475569;
  }

  @media screen {
    .print-workspace-body {
      min-width: 1280px;
    }

    .print-workspace-body--embedded {
      min-width: 0;
    }

    .print-workspace-preview .print-page {
      position: relative;
      overflow: hidden;
    }

    .print-workspace-preview .print-page .print-header,
    .print-workspace-preview .print-page .print-footer {
      position: absolute;
      left: 0;
      right: 0;
      width: 100%;
    }

    .print-workspace-preview .print-page .print-header {
      top: 0;
    }

    .print-workspace-preview .print-page .print-footer {
      bottom: 0;
      transform: none;
    }
  }

  @media print {
    .print-workspace-body {
      background: #fff;
    }

    .print-workspace {
      display: block;
      width: auto;
      max-width: none;
      padding: 0;
    }

    .print-workspace-sidebar {
      display: none !important;
    }

    .print-workspace-preview {
      display: block;
      width: auto;
    }
  }

  @media (max-width: 1400px) {
    .print-workspace {
      padding: 18px;
      gap: min(var(--print-workspace-gap, 28px), 18px);
    }
  }

  @media (max-width: 1180px) {
    .print-workspace-body {
      min-width: 0;
    }

    .print-workspace {
      width: auto;
      grid-template-columns: 1fr;
      align-items: center;
    }

    .print-workspace-sidebar {
      position: static;
      width: min(100%, 720px);
    }

    .print-workspace-preview {
      width: 100%;
    }
  }
</style>
