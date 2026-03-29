<?php

namespace App\Modules\GSO\Jobs;

use App\Modules\GSO\Models\StickerPrintJob;
use App\Modules\GSO\Services\Contracts\StickerReportServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class GenerateStickerPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function __construct(
        private readonly string $stickerPrintJobId,
    ) {
        $this->onQueue('reports');
    }

    public function handle(StickerReportServiceInterface $stickers): void
    {
        $printJob = StickerPrintJob::query()->find($this->stickerPrintJobId);

        if (! $printJob) {
            return;
        }

        $printJob->forceFill([
            'status' => StickerPrintJob::STATUS_RUNNING,
            'stage' => 'Queued job started.',
            'progress_percent' => 2,
            'started_at' => now(),
            'error_message' => null,
        ])->save();

        $fileName = 'stickers-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/sticker-print-jobs/' . $printJob->id . '/' . $fileName);

        try {
            $generatedPath = $stickers->generatePdfWithProgress(
                filters: (array) ($printJob->filters ?? []),
                progress: function (array $progress) use ($printJob): void {
                    $printJob->forceFill([
                        'status' => (string) ($progress['status'] ?? StickerPrintJob::STATUS_RUNNING),
                        'stage' => (string) ($progress['stage'] ?? $printJob->stage),
                        'progress_percent' => (int) ($progress['progress_percent'] ?? $printJob->progress_percent),
                        'total_pages' => (int) ($progress['total_pages'] ?? $printJob->total_pages),
                        'completed_pages' => (int) ($progress['completed_pages'] ?? $printJob->completed_pages),
                    ])->save();
                },
                outputPath: $outputPath,
            );

            $printJob->forceFill([
                'status' => StickerPrintJob::STATUS_COMPLETED,
                'stage' => 'Sticker PDF is ready for download.',
                'progress_percent' => 100,
                'output_path' => $generatedPath,
                'file_name' => basename($generatedPath),
                'completed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $printJob->forceFill([
                'status' => StickerPrintJob::STATUS_FAILED,
                'stage' => 'Sticker PDF generation failed.',
                'error_message' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }
}
