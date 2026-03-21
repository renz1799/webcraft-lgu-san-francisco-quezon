<?php

namespace App\Core\Builders\AuditLogs;

use App\Core\Builders\Contracts\AuditLogs\AuditLogMetaBuilderInterface;

class AuditLogMetaBuilder implements AuditLogMetaBuilderInterface
{
    public function build(array $meta = [], array $display = []): ?array
    {
        $normalizedDisplay = $this->normalizeDisplayPayload(
            $display ?: (is_array($meta['display'] ?? null) ? $meta['display'] : [])
        );

        if ($normalizedDisplay === []) {
            return $meta ?: null;
        }

        $meta['display'] = $normalizedDisplay;

        return $meta;
    }

    private function normalizeDisplayPayload(array $display): array
    {
        $summary = trim((string) ($display['summary'] ?? ''));
        $subjectLabel = trim((string) ($display['subject_label'] ?? ''));
        $sections = $this->normalizeDisplaySections($display['sections'] ?? []);
        $requestDetails = $this->normalizeDisplayMap($display['request_details'] ?? []);
        $systemNotes = $this->normalizeDisplayNotes($display['system_notes'] ?? []);

        return array_filter([
            'summary' => $summary !== '' ? $summary : null,
            'subject_label' => $subjectLabel !== '' ? $subjectLabel : null,
            'sections' => $sections,
            'request_details' => $requestDetails,
            'system_notes' => $systemNotes,
        ], static fn (mixed $value): bool => $value !== null && $value !== []);
    }

    private function normalizeDisplaySections(mixed $sections): array
    {
        if (! is_array($sections)) {
            return [];
        }

        $normalized = [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $title = trim((string) ($section['title'] ?? ''));
            $items = $this->normalizeDisplayItems($section['items'] ?? []);

            if ($title === '' || $items === []) {
                continue;
            }

            $normalized[] = [
                'title' => $title,
                'items' => $items,
            ];
        }

        return $normalized;
    }

    private function normalizeDisplayItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $row = ['label' => $label];

            if (array_key_exists('value', $item)) {
                $row['value'] = $item['value'];
            }

            if (array_key_exists('before', $item)) {
                $row['before'] = $item['before'];
            }

            if (array_key_exists('after', $item)) {
                $row['after'] = $item['after'];
            }

            if (count($row) === 1) {
                continue;
            }

            $normalized[] = $row;
        }

        return $normalized;
    }

    private function normalizeDisplayMap(mixed $details): array
    {
        if (! is_array($details)) {
            return [];
        }

        $normalized = [];

        foreach ($details as $label => $value) {
            $key = trim((string) $label);
            if ($key === '' || $value === null || $value === '' || $value === []) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function normalizeDisplayNotes(mixed $notes): array
    {
        if (! is_array($notes)) {
            return [];
        }

        $normalized = [];

        foreach ($notes as $note) {
            if (! is_array($note)) {
                continue;
            }

            $title = trim((string) ($note['title'] ?? ''));
            $items = is_array($note['items'] ?? null)
                ? array_values(array_filter($note['items'], static fn (mixed $item): bool => $item !== null && $item !== '' && $item !== []))
                : [];

            if ($title === '' || $items === []) {
                continue;
            }

            $normalized[] = [
                'title' => $title,
                'items' => $items,
            ];
        }

        return $normalized;
    }
}
