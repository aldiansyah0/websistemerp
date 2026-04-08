<?php

namespace App\Services;

use App\Models\AccountingJournalEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportExportService
{
    public function __construct(
        private readonly FinancialStatementAggregateService $financialStatementAggregateService,
    ) {
    }

    public function exportExcel(CarbonImmutable $startDate, CarbonImmutable $endDate): StreamedResponse
    {
        $filename = sprintf('jurnal-akuntansi-%s-to-%s.csv', $startDate->format('Ymd'), $endDate->format('Ymd'));
        $chunkSize = $this->chunkSize();

        return response()->streamDownload(function () use ($startDate, $endDate, $chunkSize): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'Entry Number',
                'Entry Date',
                'Reference Type',
                'Reference ID',
                'Description',
                'Account Code',
                'Account Name',
                'Debit',
                'Credit',
            ]);

            $this->journalEntryQuery($startDate, $endDate)
                ->chunkById($chunkSize, function ($entries) use ($handle): void {
                    foreach ($entries as $entry) {
                        foreach ($entry->lines as $line) {
                            fputcsv($handle, [
                                $entry->entry_number,
                                $entry->entry_date?->format('Y-m-d'),
                                $entry->reference_type,
                                $entry->reference_id,
                                $entry->description,
                                $line->account_code,
                                $line->account_name,
                                (float) $line->debit,
                                (float) $line->credit,
                            ]);
                        }
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{file_path: string, file_name: string}
     */
    public function generateExcelFile(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $filename = sprintf('jurnal-akuntansi-%s-to-%s.csv', $startDate->format('Ymd'), $endDate->format('Ymd'));
        $relativePath = 'exports/' . $startDate->format('Ymd') . '/' . uniqid('report_', true) . '-' . $filename;
        $absolutePath = Storage::disk('local')->path($relativePath);
        $directory = dirname($absolutePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($absolutePath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Tidak dapat membuat file export CSV.');
        }

        fputcsv($handle, [
            'Entry Number',
            'Entry Date',
            'Reference Type',
            'Reference ID',
            'Description',
            'Account Code',
            'Account Name',
            'Debit',
            'Credit',
        ]);

        $chunkSize = $this->chunkSize();
        $this->journalEntryQuery($startDate, $endDate)
            ->chunkById($chunkSize, function ($entries) use ($handle): void {
                foreach ($entries as $entry) {
                    foreach ($entry->lines as $line) {
                        fputcsv($handle, [
                            $entry->entry_number,
                            $entry->entry_date?->format('Y-m-d'),
                            $entry->reference_type,
                            $entry->reference_id,
                            $entry->description,
                            $line->account_code,
                            $line->account_name,
                            (float) $line->debit,
                            (float) $line->credit,
                        ]);
                    }
                }
            });

        fclose($handle);

        return [
            'file_path' => $relativePath,
            'file_name' => $filename,
        ];
    }

    public function exportPdf(CarbonImmutable $startDate, CarbonImmutable $endDate)
    {
        $chunkSize = $this->chunkSize();
        $profitLoss = $this->financialStatementAggregateService->profitLossSummaryChunked($startDate, $endDate, $chunkSize);
        $balanceSheet = $this->financialStatementAggregateService->balanceSheetSummaryChunked($endDate, $chunkSize);
        $journalSummary = $this->journalSummaryChunked($startDate, $endDate, $chunkSize);
        $journalPreview = $this->journalEntryQuery($startDate, $endDate)
            ->limit(20)
            ->get();

        $pdf = Pdf::loadView('exports.financial-report-pdf', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'profitLoss' => $profitLoss,
            'balanceSheet' => $balanceSheet,
            'journalSummary' => $journalSummary,
            'journalPreview' => $journalPreview,
            'generatedAt' => CarbonImmutable::now('Asia/Jakarta'),
            'chunkSize' => $chunkSize,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('financial-report-%s-to-%s.pdf', $startDate->format('Ymd'), $endDate->format('Ymd'));

        return $pdf->download($filename);
    }

    /**
     * @return array{file_path: string, file_name: string}
     */
    public function generatePdfFile(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $chunkSize = $this->chunkSize();
        $profitLoss = $this->financialStatementAggregateService->profitLossSummaryChunked($startDate, $endDate, $chunkSize);
        $balanceSheet = $this->financialStatementAggregateService->balanceSheetSummaryChunked($endDate, $chunkSize);
        $journalSummary = $this->journalSummaryChunked($startDate, $endDate, $chunkSize);
        $journalPreview = $this->journalEntryQuery($startDate, $endDate)
            ->limit(20)
            ->get();

        $pdf = Pdf::loadView('exports.financial-report-pdf', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'profitLoss' => $profitLoss,
            'balanceSheet' => $balanceSheet,
            'journalSummary' => $journalSummary,
            'journalPreview' => $journalPreview,
            'generatedAt' => CarbonImmutable::now('Asia/Jakarta'),
            'chunkSize' => $chunkSize,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('financial-report-%s-to-%s.pdf', $startDate->format('Ymd'), $endDate->format('Ymd'));
        $relativePath = 'exports/' . $startDate->format('Ymd') . '/' . uniqid('report_', true) . '-' . $filename;
        $absolutePath = Storage::disk('local')->path($relativePath);
        $directory = dirname($absolutePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save($absolutePath);

        return [
            'file_path' => $relativePath,
            'file_name' => $filename,
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private function journalSummaryChunked(CarbonImmutable $startDate, CarbonImmutable $endDate, int $chunkSize): array
    {
        $summary = [
            'entries_count' => 0,
            'line_count' => 0,
            'total_debit' => 0.0,
            'total_credit' => 0.0,
        ];

        $this->journalEntryQuery($startDate, $endDate)
            ->chunkById($chunkSize, function ($entries) use (&$summary): void {
                foreach ($entries as $entry) {
                    $summary['entries_count']++;
                    $summary['total_debit'] += (float) $entry->total_debit;
                    $summary['total_credit'] += (float) $entry->total_credit;
                    $summary['line_count'] += $entry->lines->count();
                }
            });

        return $summary;
    }

    private function journalEntryQuery(CarbonImmutable $startDate, CarbonImmutable $endDate)
    {
        return AccountingJournalEntry::query()
            ->with('lines')
            ->whereBetween('entry_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('id');
    }

    private function chunkSize(): int
    {
        return max((int) config('erp.export.chunk_size', 1000), 100);
    }
}
