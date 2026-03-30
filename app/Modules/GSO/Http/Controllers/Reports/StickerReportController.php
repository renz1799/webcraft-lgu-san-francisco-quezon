<?php

namespace App\Modules\GSO\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Core\Support\AdminContextAuthorizer;
use App\Modules\GSO\Http\Requests\Reports\PrintStickerReportRequest;
use App\Modules\GSO\Jobs\GenerateStickerPdfJob;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\StickerPrintJob;
use App\Modules\GSO\Services\Contracts\StickerReportServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StickerReportController extends Controller
{
    public function __construct(
        private readonly StickerReportServiceInterface $stickers,
    ) {
        $this->middleware('permission:reports.stickers.view|inventory_items.view|inventory_items.update');
    }

    public function print(PrintStickerReportRequest $request): View
    {
        $validated = $request->validated();
        $data = $this->stickers->getPrintViewData($validated);

        return view('gso::reports.stickers.print.index', [
            'report' => $data['report'],
            'selectedInventoryItem' => $data['selectedInventoryItem'],
            'selectedInventoryItems' => $data['selectedInventoryItems'] ?? collect(),
            'selectedStickers' => $data['selectedStickers'] ?? [],
            'availableInventoryItems' => $data['availableInventoryItems'],
            'sticker' => $data['sticker'],
            'stickers' => $data['stickers'],
            'controls' => $data['controls'],
            'sheet' => $data['sheet'],
            'filters' => $validated,
        ]);
    }

    public function downloadPdf(PrintStickerReportRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();
        $path = $this->stickers->generatePdf($validated);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function startPdfJob(PrintStickerReportRequest $request): JsonResponse
    {
        $this->cleanupStaleLocalStickerJobs();

        $validated = $request->validated();
        $selectedInventoryItemIds = collect($validated['inventory_item_ids'] ?? [])
            ->filter()
            ->values()
            ->all();

        if ($selectedInventoryItemIds === []) {
            return response()->json([
                'message' => 'Select at least one inventory item before generating the sticker PDF.',
            ], 422);
        }

        $printJob = StickerPrintJob::query()->create([
            'id' => (string) Str::uuid(),
            'requested_by' => auth()->id(),
            'status' => StickerPrintJob::STATUS_QUEUED,
            'stage' => 'Queued for PDF generation.',
            'progress_percent' => 0,
            'total_pages' => 0,
            'completed_pages' => 0,
            'filters' => $validated,
        ]);

        if ($this->shouldUseDetachedLocalStickerProcess()) {
            if (! $this->startDetachedLocalStickerProcess($printJob->id)) {
                $printJob->forceFill([
                    'status' => StickerPrintJob::STATUS_FAILED,
                    'stage' => 'The local sticker PDF worker could not be started.',
                    'error_message' => 'The local sticker PDF worker could not be started.',
                ])->save();

                return response()->json([
                    'message' => 'The local sticker PDF worker could not be started. Please try again.',
                    'job_id' => $printJob->id,
                    'status' => $printJob->status,
                    'stage' => $printJob->stage,
                    'progress_percent' => $printJob->progress_percent,
                    'total_pages' => $printJob->total_pages,
                    'completed_pages' => $printJob->completed_pages,
                    'error_message' => $printJob->error_message,
                ], 500);
            }
        } else {
            GenerateStickerPdfJob::dispatch($printJob->id);
        }

        return response()->json([
            'job_id' => $printJob->id,
            'status' => $printJob->status,
            'stage' => $printJob->stage,
            'progress_percent' => $printJob->progress_percent,
            'total_pages' => $printJob->total_pages,
            'completed_pages' => $printJob->completed_pages,
            'status_url' => route('gso.reports.stickers.jobs.show', ['stickerPrintJob' => $printJob->id]),
            'download_url' => route('gso.reports.stickers.jobs.download', ['stickerPrintJob' => $printJob->id]),
        ], 202);
    }

    public function pdfJobStatus(StickerPrintJob $stickerPrintJob): JsonResponse
    {
        $this->authorizePrintJob($stickerPrintJob);
        $this->cleanupStaleLocalStickerJobs();
        $stickerPrintJob->refresh();

        return response()->json([
            'job_id' => $stickerPrintJob->id,
            'status' => $stickerPrintJob->status,
            'stage' => $stickerPrintJob->stage,
            'progress_percent' => (int) $stickerPrintJob->progress_percent,
            'total_pages' => (int) $stickerPrintJob->total_pages,
            'completed_pages' => (int) $stickerPrintJob->completed_pages,
            'error_message' => $stickerPrintJob->error_message,
            'download_url' => $stickerPrintJob->status === StickerPrintJob::STATUS_COMPLETED
                ? route('gso.reports.stickers.jobs.download', ['stickerPrintJob' => $stickerPrintJob->id])
                : null,
        ]);
    }

    public function downloadGeneratedPdf(StickerPrintJob $stickerPrintJob): BinaryFileResponse
    {
        $this->authorizePrintJob($stickerPrintJob);

        abort_unless(
            $stickerPrintJob->status === StickerPrintJob::STATUS_COMPLETED
                && is_string($stickerPrintJob->output_path)
                && is_file($stickerPrintJob->output_path),
            404,
        );

        return response()->download(
            $stickerPrintJob->output_path,
            $stickerPrintJob->file_name ?: basename($stickerPrintJob->output_path),
        );
    }

    public function fromInventoryItem(PrintStickerReportRequest $request, string $inventoryItem): RedirectResponse
    {
        $resolvedInventoryItem = InventoryItem::query()
            ->withTrashed()
            ->findOrFail($inventoryItem, ['id']);

        $validated = $request->validated();

        return redirect()->route('gso.reports.stickers.print', array_filter([
            'preview' => 1,
            'inventory_item_ids' => [(string) $resolvedInventoryItem->id],
            'copies' => $validated['copies'] ?? request()->query('copies'),
            'show_cut_guides' => array_key_exists('show_cut_guides', $validated)
                ? (int) ((bool) $validated['show_cut_guides'])
                : request()->query('show_cut_guides'),
        ], static fn ($value) => $value !== null && $value !== ''));
    }

    private function authorizePrintJob(StickerPrintJob $stickerPrintJob): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        if ((string) $stickerPrintJob->requested_by === (string) $user->getAuthIdentifier()) {
            return;
        }

        if (app(AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'reports.stickers.view',
            'inventory_items.view',
            'inventory_items.update',
        ])) {
            return;
        }

        abort(403);
    }

    private function shouldUseDetachedLocalStickerProcess(): bool
    {
        return ! app()->runningUnitTests()
            && app()->environment('local')
            && config('queue.default') === 'database';
    }

    private function startDetachedLocalStickerProcess(string $jobId): bool
    {
        $phpBinary = $this->resolveCliPhpBinary();
        if ($phpBinary === null) {
            return false;
        }

        $artisan = base_path('artisan');
        $command = sprintf(
            '"%s" "%s" gso:stickers:process "%s"',
            $phpBinary,
            $artisan,
            $jobId,
        );

        if (DIRECTORY_SEPARATOR === '\\') {
            return @pclose(@popen('start "" /B ' . $command, 'r')) !== false;
        }

        return @pclose(@popen($command . ' > /dev/null 2>&1 &', 'r')) !== false;
    }

    private function resolveCliPhpBinary(): ?string
    {
        $binary = PHP_BINARY;

        if (! is_string($binary) || $binary === '') {
            return null;
        }

        if (DIRECTORY_SEPARATOR === '\\' && str_ends_with(strtolower($binary), 'php-cgi.exe')) {
            $cliBinary = dirname($binary) . DIRECTORY_SEPARATOR . 'php.exe';

            if (is_file($cliBinary)) {
                return $cliBinary;
            }
        }

        return $binary;
    }

    private function cleanupStaleLocalStickerJobs(): void
    {
        if (! $this->shouldUseDetachedLocalStickerProcess()) {
            return;
        }

        $cutoff = now()->subMinutes(10);

        StickerPrintJob::query()
            ->whereIn('status', [
                StickerPrintJob::STATUS_QUEUED,
                StickerPrintJob::STATUS_RUNNING,
            ])
            ->where(function ($query) use ($cutoff): void {
                $query->where('created_at', '<=', $cutoff)
                    ->orWhere(function ($runningQuery) use ($cutoff): void {
                        $runningQuery->where('status', StickerPrintJob::STATUS_RUNNING)
                            ->where('started_at', '<=', $cutoff);
                    });
            })
            ->update([
                'status' => StickerPrintJob::STATUS_FAILED,
                'stage' => 'The local sticker PDF job timed out and was marked as failed.',
                'error_message' => 'The local sticker PDF job timed out and was marked as failed.',
                'completed_at' => now(),
            ]);

        DB::table('jobs')
            ->where('queue', 'reports')
            ->where('payload', 'like', '%GenerateStickerPdfJob%')
            ->where('created_at', '<=', $cutoff->timestamp)
            ->delete();
    }
}
