<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class PrintWorkspaceSampleController extends Controller
{
    public function rpcppe(Request $request)
    {
        return view('print-workspace.rpcppe-sample', $this->buildRpcppeViewData($request));
    }

    public function rpcppePdf(Request $request)
    {
        $viewData = $this->buildRpcppeViewData($request, true);

        $tempRoot = storage_path('app/tmp/rpcppe-sample');
        $token = (string) Str::uuid();
        $workingDir = $tempRoot.DIRECTORY_SEPARATOR.$token;
        $profileDir = $workingDir.DIRECTORY_SEPARATOR.'chrome-profile';
        $htmlPath = $workingDir.DIRECTORY_SEPARATOR.'rpcppe-sample.html';
        $pdfPath = $workingDir.DIRECTORY_SEPARATOR.'rpcppe-sample.pdf';

        File::ensureDirectoryExists($profileDir);

        try {
            File::put($htmlPath, view('print-workspace.rpcppe-sample-pdf', $viewData)->render());

            $process = new Process([
                $this->resolveChromeBinary(),
                '--user-data-dir='.$profileDir,
                '--headless=new',
                '--disable-gpu',
                '--no-pdf-header-footer',
                '--print-to-pdf='.$pdfPath,
                $this->toFileUrl($htmlPath),
            ]);

            $process->setTimeout(120);
            $process->run();

            if (! is_file($pdfPath)) {
                throw new \RuntimeException(
                    'Unable to generate RPCPPE PDF sample. '.$process->getErrorOutput()
                );
            }

            $pdfContents = (string) file_get_contents($pdfPath);
        } finally {
            File::deleteDirectory($workingDir);
        }

        $fileName = 'rpcppe-preview-'.$viewData['report']['as_of'].'.pdf';

        return response($pdfContents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function buildRpcppeViewData(Request $request, bool $forPdf = false): array
    {
        $mock = $this->loadMockData();

        $fundSources = Collection::make($mock['fund_sources'] ?? []);
        $departments = Collection::make($mock['departments'] ?? []);
        $officers = Collection::make($mock['accountable_officers'] ?? []);
        $rows = Collection::make($mock['rows'] ?? []);

        $selectedFundSourceId = trim((string) $request->query('fund_source_id', ''));
        $selectedDepartmentId = trim((string) $request->query('department_id', ''));
        $selectedOfficerId = trim((string) $request->query('accountable_officer_id', ''));
        $asOf = trim((string) $request->query('as_of', now()->toDateString()));
        $prefillCount = $request->boolean('prefill_count');
        $recordCount = max(1, min(60, (int) $request->query('record_count', 20)));

        $rows = $this->expandMockRows($rows, $recordCount);

        $selectedFund = $fundSources->firstWhere('id', $selectedFundSourceId);
        $selectedDepartment = $departments->firstWhere('id', $selectedDepartmentId);
        $selectedOfficer = $officers->firstWhere('id', $selectedOfficerId);

        $filteredRows = $rows
            ->filter(function (array $row) use ($selectedFundSourceId, $selectedDepartmentId, $selectedOfficerId): bool {
                if ($selectedFundSourceId !== '' && (string) ($row['fund_source_id'] ?? '') !== $selectedFundSourceId) {
                    return false;
                }

                if ($selectedDepartmentId !== '' && (string) ($row['department_id'] ?? '') !== $selectedDepartmentId) {
                    return false;
                }

                if ($selectedOfficerId !== '' && (string) ($row['accountable_officer_id'] ?? '') !== $selectedOfficerId) {
                    return false;
                }

                return true;
            })
            ->map(function (array $row) use ($prefillCount): array {
                $bookQty = (int) ($row['balance_per_card_qty'] ?? 0);
                $unitValue = (float) ($row['unit_value'] ?? 0);
                $countQty = $prefillCount ? $bookQty : null;
                $shortageOverageQty = $prefillCount ? 0 : null;
                $shortageOverageValue = $prefillCount ? 0.0 : null;

                return $row + [
                    'count_qty' => $countQty,
                    'shortage_overage_qty' => $shortageOverageQty,
                    'shortage_overage_value' => $shortageOverageValue,
                    'book_value' => $bookQty * $unitValue,
                ];
            })
            ->values();

        $reportDate = $this->safeDate($asOf);
        $defaultSignatories = $mock['signatories'] ?? [];

        $signatories = [
            'accountable_officer_name' => trim((string) $request->query(
                'accountable_officer_name',
                $selectedOfficer['name'] ?? ($defaultSignatories['accountable_officer_name'] ?? '')
            )),
            'accountable_officer_designation' => trim((string) $request->query(
                'accountable_officer_designation',
                $selectedOfficer['designation'] ?? ($defaultSignatories['accountable_officer_designation'] ?? '')
            )),
            'committee_chair_name' => trim((string) $request->query(
                'committee_chair_name',
                $defaultSignatories['committee_chair_name'] ?? ''
            )),
            'committee_member_1_name' => trim((string) $request->query(
                'committee_member_1_name',
                $defaultSignatories['committee_member_1_name'] ?? ''
            )),
            'committee_member_2_name' => trim((string) $request->query(
                'committee_member_2_name',
                $defaultSignatories['committee_member_2_name'] ?? ''
            )),
            'approved_by_name' => trim((string) $request->query(
                'approved_by_name',
                $defaultSignatories['approved_by_name'] ?? ''
            )),
            'approved_by_designation' => trim((string) $request->query(
                'approved_by_designation',
                $defaultSignatories['approved_by_designation'] ?? ''
            )),
            'verified_by_name' => trim((string) $request->query(
                'verified_by_name',
                $defaultSignatories['verified_by_name'] ?? ''
            )),
            'verified_by_designation' => trim((string) $request->query(
                'verified_by_designation',
                $defaultSignatories['verified_by_designation'] ?? ''
            )),
        ];

        $resolvedFundCluster = $this->resolveFundClusterLabel($selectedFund, $filteredRows, $fundSources);

        $report = [
            'entity_name' => $mock['entity_name'] ?? 'LGU San Francisco',
            'appendix_label' => 'RPCPPE',
            'fund_source_id' => $selectedFundSourceId,
            'department_id' => $selectedDepartmentId,
            'accountable_officer_id' => $selectedOfficerId,
            'fund_source' => $selectedFund['label'] ?? 'All Fund Sources',
            'fund_cluster' => $resolvedFundCluster,
            'department' => $selectedDepartment['label'] ?? 'All Offices',
            'accountable_officer' => $selectedOfficer['label'] ?? 'All Accountable Officers',
            'as_of' => $reportDate->toDateString(),
            'as_of_label' => $reportDate->format('F j, Y'),
            'prefill_count' => $prefillCount,
            'record_count' => $recordCount,
            'summary' => [
                'offices_covered' => $filteredRows->pluck('department_id')->filter()->unique()->count(),
                'total_items' => $filteredRows->count(),
                'total_book_value' => (float) $filteredRows->sum('book_value'),
            ],
            'signatories' => $signatories,
        ];

        return [
            'report' => $report,
            'rows' => $filteredRows->all(),
            'available_funds' => $fundSources->all(),
            'available_departments' => $departments->all(),
            'available_accountable_officers' => $officers->all(),
            'isPreview' => ! $forPdf,
            'assetUrls' => $this->resolveAssetUrls($forPdf),
        ];
    }

    private function loadMockData(): array
    {
        $path = resource_path('mock-data/print-workspace/rpcppe-sample.json');

        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function safeDate(string $value): Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return Carbon::now();
        }
    }

    private function expandMockRows(Collection $rows, int $targetCount): Collection
    {
        $rows = $rows->values();

        if ($rows->isEmpty()) {
            return $rows;
        }

        return collect(range(1, $targetCount))
            ->map(function (int $index) use ($rows): array {
                $baseRow = (array) $rows->get(($index - 1) % $rows->count(), []);
                $sequence = str_pad((string) $index, 5, '0', STR_PAD_LEFT);

                $baseRow['id'] = 'row-'.$index;
                $baseRow['property_no'] = preg_replace('/\d+$/', $sequence, (string) ($baseRow['property_no'] ?? '')) ?: '2026-PPE-02-PPE-'.$sequence;

                return $baseRow;
            })
            ->values();
    }

    private function resolveFundClusterLabel(?array $selectedFund, Collection $filteredRows, Collection $fundSources): string
    {
        if ($selectedFund && !empty($selectedFund['cluster'])) {
            return (string) $selectedFund['cluster'];
        }

        $rowFundIds = $filteredRows->pluck('fund_source_id')->filter()->unique()->values();

        if ($rowFundIds->count() === 1) {
            $matchedFund = $fundSources->firstWhere('id', (string) $rowFundIds->first());

            if ($matchedFund && !empty($matchedFund['cluster'])) {
                return (string) $matchedFund['cluster'];
            }
        }

        return $rowFundIds->count() > 1 ? 'Multiple Fund Clusters' : '01 - General Fund';
    }

    private function resolveAssetUrls(bool $forPdf): array
    {
        $headerPath = public_path('headers/a4_landscape_header_dark_3508x300.png');
        $footerPath = public_path('headers/a4_landscape_footer_dark_3508x250.png');

        if ($forPdf) {
            return [
                'header' => $this->toFileUrl($headerPath),
                'footer' => $this->toFileUrl($footerPath),
            ];
        }

        return [
            'header' => asset('headers/a4_landscape_header_dark_3508x300.png'),
            'footer' => asset('headers/a4_landscape_footer_dark_3508x250.png'),
        ];
    }

    private function resolveChromeBinary(): string
    {
        $candidates = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Unable to locate a Chrome-based browser for PDF generation.');
    }

    private function toFileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if (preg_match('/^([A-Za-z]):\/(.*)$/', $normalized, $matches) === 1) {
            $segments = array_map(
                static fn (string $segment): string => rawurlencode($segment),
                array_values(array_filter(explode('/', $matches[2]), static fn (string $segment): bool => $segment !== ''))
            );

            return 'file:///'.$matches[1].':/'.implode('/', $segments);
        }

        return 'file:///'.$normalized;
    }
}
