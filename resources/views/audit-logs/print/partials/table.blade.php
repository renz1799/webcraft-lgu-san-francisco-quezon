<table class="audit-print-table">
    <thead>
        <tr>
            <th>Date/Time</th>
            <th>Module</th>
            <th>Action</th>
            <th>Message</th>
            <th>Actor</th>
            <th>Subject</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['datetime'] ?? '-' }}</td>
                <td>{{ $row['module'] ?? '-' }}</td>
                <td>{{ $row['action'] ?? '-' }}</td>
                <td>{{ $row['message'] ?? '-' }}</td>
                <td>{{ $row['actor_name'] ?? '-' }}</td>
                <td>{{ $row['subject'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="audit-print-empty">
                    No audit log records found for the selected filters.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
