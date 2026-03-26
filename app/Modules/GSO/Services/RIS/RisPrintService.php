<?php

namespace App\Modules\GSO\Services\RIS;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;
use Illuminate\Support\Str;

class RisPrintService implements RisPrintServiceInterface
{
    public function __construct(
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PrintConfigLoaderInterface $printConfigLoader,
    ) {
    }

    public function buildReport(string $risId, ?string $requestedPaper = null): array
    {
        $ris = Ris::query()
            ->withTrashed()
            ->with([
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'items' => fn ($query) => $query
                    ->orderBy('line_no')
                    ->orderBy('created_at'),
            ])
            ->findOrFail($risId);

        $rows = $this->buildRows($ris);
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_ris', $requestedPaper);
        $report = [
            'title' => 'Requisition and Issue Slip',
            'ris' => $this->buildRisSummary($ris),
            'document' => $this->buildDocumentMeta($ris, $rows),
            'rows' => $rows,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $risId, ?string $requestedPaper = null): string
    {
        $payload = $this->buildReport($risId, $requestedPaper);

        $filename = 'ris-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::ris.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRows(Ris $ris): array
    {
        return $ris->items
            ->sortBy(fn ($item) => $item->line_no ?? 999999)
            ->values()
            ->map(fn ($item) => [
                'stock_no' => $this->nullableTrim($item->stock_no_snapshot) ?? '',
                'unit' => $this->nullableTrim($item->unit_snapshot) ?? '',
                'description' => $this->nullableTrim($item->description_snapshot)
                    ?? $this->nullableTrim($item->item_name_snapshot)
                    ?? '',
                'qty_requested' => max(0, (int) ($item->qty_requested ?? 0)),
                'qty_issued' => max(0, (int) ($item->qty_issued ?? 0)),
                'remarks' => $this->nullableTrim($item->remarks) ?? '',
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildDocumentMeta(Ris $ris, array $rows): array
    {
        return [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'Appendix 48',
            'title' => 'Requisition and Issue Slip',
            'ris_no' => $this->nullableTrim($ris->ris_number) ?? '',
            'ris_date' => $ris->ris_date?->toDateString(),
            'ris_date_label' => $ris->ris_date?->format('m/d/Y') ?? '',
            'office' => $this->departmentLabel($ris),
            'fund' => $this->fundLabel($ris),
            'division' => $this->nullableTrim($ris->division) ?? '',
            'fpp_code' => $this->nullableTrim($ris->fpp_code) ?? '',
            'responsibility_center_code' => $this->nullableTrim($ris->responsibility_center_code) ?? '',
            'purpose' => $this->nullableTrim($ris->purpose) ?? '',
            'remarks' => $this->nullableTrim($ris->remarks) ?? '',
            'requested_by_name' => $this->nullableTrim($ris->requested_by_name) ?? '',
            'requested_by_designation' => $this->nullableTrim($ris->requested_by_designation) ?? '',
            'requested_by_date_label' => $ris->requested_by_date?->format('m/d/Y') ?? '',
            'approved_by_name' => $this->nullableTrim($ris->approved_by_name) ?? '',
            'approved_by_designation' => $this->nullableTrim($ris->approved_by_designation) ?? '',
            'approved_by_date_label' => $ris->approved_by_date?->format('m/d/Y') ?? '',
            'issued_by_name' => $this->nullableTrim($ris->issued_by_name) ?? '',
            'issued_by_designation' => $this->nullableTrim($ris->issued_by_designation) ?? '',
            'issued_by_date_label' => $ris->issued_by_date?->format('m/d/Y') ?? '',
            'received_by_name' => $this->nullableTrim($ris->received_by_name) ?? '',
            'received_by_designation' => $this->nullableTrim($ris->received_by_designation) ?? '',
            'received_by_date_label' => $ris->received_by_date?->format('m/d/Y') ?? '',
            'summary' => [
                'line_items' => count($rows),
                'printed_rows' => count($rows),
                'qty_requested_total' => array_sum(array_map(
                    static fn (array $row): int => (int) ($row['qty_requested'] ?? 0),
                    $rows,
                )),
                'qty_issued_total' => array_sum(array_map(
                    static fn (array $row): int => (int) ($row['qty_issued'] ?? 0),
                    $rows,
                )),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRisSummary(Ris $ris): array
    {
        $label = $this->nullableTrim($ris->ris_number)
            ?? $this->nullableTrim($ris->requesting_department_name_snapshot)
            ?? 'RIS Record';

        return [
            'id' => (string) $ris->id,
            'label' => $label,
            'status' => (string) ($ris->status ?? ''),
            'status_text' => strtoupper((string) ($ris->status ?? 'draft')),
            'is_archived' => $ris->trashed(),
        ];
    }

    private function departmentLabel(Ris $ris): string
    {
        $snapshotCode = $this->nullableTrim($ris->requesting_department_code_snapshot);
        $snapshotName = $this->nullableTrim($ris->requesting_department_name_snapshot);
        $relatedCode = $this->nullableTrim($ris->department?->code);
        $relatedName = $this->nullableTrim($ris->department?->name);

        if ($snapshotCode !== null && $snapshotName !== null) {
            return "{$snapshotCode} - {$snapshotName}";
        }

        if ($snapshotName !== null) {
            return $snapshotName;
        }

        if ($relatedCode !== null && $relatedName !== null) {
            return "{$relatedCode} - {$relatedName}";
        }

        return $relatedCode ?? $relatedName ?? '';
    }

    private function fundLabel(Ris $ris): string
    {
        $code = $this->nullableTrim($ris->fundSource?->code);
        $name = $this->nullableTrim($ris->fundSource?->name);
        $fallback = $this->nullableTrim($ris->fund);

        if ($code !== null && $name !== null) {
            return "{$code} - {$name}";
        }

        return $name ?? $code ?? $fallback ?? '';
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
