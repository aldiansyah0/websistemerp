<?php

namespace App\Jobs;

use App\Models\ReportExport;
use App\Services\AuditLogService;
use App\Services\FinancialReportExportService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateFinancialReportExportJob implements ShouldQueue
{
    use Queueable;

    public int $tries;
    public int $timeout;
    public int $maxExceptions = 3;

    public function __construct(
        public readonly int $reportExportId,
    ) {
        $this->tries = max((int) config('erp.export.tries', 3), 1);
        $this->timeout = max((int) config('erp.export.timeout', 300), 60);
        $this->onQueue((string) config('erp.export.queue', 'reports'));
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        $base = max((int) config('erp.export.backoff_seconds', 10), 1);

        return [$base, $base * 2, $base * 3];
    }

    public function retryUntil(): DateTimeInterface
    {
        $minutes = max((int) config('erp.export.retry_window_minutes', 30), 1);

        return now()->addMinutes($minutes);
    }

    public function handle(FinancialReportExportService $financialReportExportService, AuditLogService $auditLogService): void
    {
        $reportExport = ReportExport::query()->withoutTenantLocation()->find($this->reportExportId);

        if ($reportExport === null) {
            return;
        }

        $reportExport->status = ReportExport::STATUS_PROCESSING;
        $reportExport->started_at = now('Asia/Jakarta');
        $reportExport->error_message = null;
        $reportExport->save();

        try {
            $startDate = CarbonImmutable::parse((string) ($reportExport->filters['start_date'] ?? now()->toDateString()))->startOfDay();
            $endDate = CarbonImmutable::parse((string) ($reportExport->filters['end_date'] ?? now()->toDateString()))->endOfDay();

            $result = $reportExport->format === 'pdf'
                ? $financialReportExportService->generatePdfFile($startDate, $endDate)
                : $financialReportExportService->generateExcelFile($startDate, $endDate);

            $reportExport->status = ReportExport::STATUS_COMPLETED;
            $reportExport->file_path = $result['file_path'];
            $reportExport->file_name = $result['file_name'];
            $reportExport->finished_at = now('Asia/Jakarta');
            $reportExport->save();

            $auditLogService->log('finance', 'report_export.completed', 'Export laporan keuangan selesai', $reportExport, [
                'report_type' => $reportExport->report_type,
                'format' => $reportExport->format,
                'file_path' => $reportExport->file_path,
            ]);
        } catch (Throwable $exception) {
            $reportExport->status = ReportExport::STATUS_FAILED;
            $reportExport->error_message = $exception->getMessage();
            $reportExport->finished_at = now('Asia/Jakarta');
            $reportExport->save();

            $auditLogService->log('finance', 'report_export.failed', 'Export laporan keuangan gagal', $reportExport, [
                'report_type' => $reportExport->report_type,
                'format' => $reportExport->format,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('alerts')->critical('Financial report export job failed permanently', [
            'report_export_id' => $this->reportExportId,
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
        ]);
    }
}
