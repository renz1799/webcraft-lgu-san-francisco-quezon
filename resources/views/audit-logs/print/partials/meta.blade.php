<div class="audit-print-meta">
    <h1 class="audit-print-title">{{ $report->title }}</h1>

    <div class="audit-print-meta-grid">
        <div><strong>Generated At:</strong> {{ $report->generatedAt }}</div>
        <div><strong>Total Records:</strong> {{ $report->total }}</div>
        <div><strong>Date From:</strong> {{ $report->filters['date_from'] ?? '-' }}</div>
        <div><strong>Date To:</strong> {{ $report->filters['date_to'] ?? '-' }}</div>
        <div><strong>Module:</strong> {{ $report->filters['module_name'] ?? '-' }}</div>
        <div><strong>Action:</strong> {{ $report->filters['action'] ?? '-' }}</div>
        <div><strong>Subject Type:</strong> {{ $report->filters['subject_type'] ?? '-' }}</div>
        <div><strong>Search:</strong> {{ $report->filters['search'] ?? '-' }}</div>
    </div>
</div>