<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialReportExportRequest;
use App\Jobs\GenerateFinancialReportExportJob;
use App\Models\ReportExport;
use App\Services\FinancialReportExportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class FinancialReportExportController extends Controller
{
    public function export(FinancialReportExportRequest $request, FinancialReportExportService $financialReportExportService)
    {
        $payload = $request->validated();
        $startDate = CarbonImmutable::parse((string) $payload['start_date'])->startOfDay();
        $endDate = CarbonImmutable::parse((string) $payload['end_date'])->endOfDay();

        if ($payload['format'] === 'pdf') {
            return $financialReportExportService->exportPdf($startDate, $endDate);
        }

        return $financialReportExportService->exportExcel($startDate, $endDate);
    }

    public function queue(FinancialReportExportRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $reportExport = ReportExport::query()->withoutTenantLocation()->create([
            'tenant_id' => auth()->user()?->tenant_id,
            'location_id' => auth()->user()?->location_id,
            'requested_by' => auth()->id(),
            'report_type' => 'financial_report',
            'format' => $payload['format'],
            'filters' => [
                'start_date' => $payload['start_date'],
                'end_date' => $payload['end_date'],
            ],
            'status' => ReportExport::STATUS_PENDING,
        ]);

        GenerateFinancialReportExportJob::dispatch((int) $reportExport->id)
            ->onQueue((string) config('erp.export.queue', 'reports'));

        return redirect()
            ->route('financial-report')
            ->with('success', 'Export laporan dijadwalkan. Lacak status job #' . $reportExport->id . ' di panel export.');
    }

    public function status(ReportExport $reportExport): JsonResponse
    {
        return response()->json([
            'id' => $reportExport->id,
            'status' => $reportExport->status,
            'file_name' => $reportExport->file_name,
            'error_message' => $reportExport->error_message,
            'started_at' => $reportExport->started_at?->toDateTimeString(),
            'finished_at' => $reportExport->finished_at?->toDateTimeString(),
        ]);
    }

    public function download(ReportExport $reportExport)
    {
        if ($reportExport->status !== ReportExport::STATUS_COMPLETED || blank($reportExport->file_path)) {
            abort(404, 'File export belum tersedia.');
        }

        if (! Storage::disk('local')->exists($reportExport->file_path)) {
            abort(404, 'File export tidak ditemukan di storage.');
        }

        return Storage::disk('local')->download($reportExport->file_path, $reportExport->file_name ?? basename($reportExport->file_path));
    }
}
