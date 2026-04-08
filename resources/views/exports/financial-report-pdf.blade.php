<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }
        h1, h2 {
            margin: 0 0 8px 0;
        }
        .meta {
            margin-bottom: 14px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
        }
        .section {
            margin-bottom: 18px;
        }
        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Laporan Keuangan Otomatis ERP</h1>
    <p class="meta">
        Periode: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}<br>
        Generated: {{ $generatedAt->format('d M Y H:i') }} WIB<br>
        Export Engine Chunk Size: {{ number_format($chunkSize, 0, ',', '.') }} rows
    </p>

    <div class="section">
        <h2>Laba Rugi (Aggregate Table)</h2>
        <table>
            <tr><th>Pos</th><th>Nilai</th></tr>
            <tr><td>Pendapatan</td><td class="amount">Rp {{ number_format($profitLoss['revenue_amount'], 2, ',', '.') }}</td></tr>
            <tr><td>Harga Pokok Penjualan</td><td class="amount">Rp {{ number_format($profitLoss['cogs_amount'], 2, ',', '.') }}</td></tr>
            <tr><td>Laba Kotor</td><td class="amount">Rp {{ number_format($profitLoss['gross_profit_amount'], 2, ',', '.') }}</td></tr>
            <tr><td>Beban Payroll</td><td class="amount">Rp {{ number_format($profitLoss['payroll_expense_amount'], 2, ',', '.') }}</td></tr>
            <tr><td>Beban Operasional</td><td class="amount">Rp {{ number_format($profitLoss['operating_expense_amount'], 2, ',', '.') }}</td></tr>
            <tr><td>Pendapatan Lain</td><td class="amount">Rp {{ number_format($profitLoss['other_income_amount'], 2, ',', '.') }}</td></tr>
            <tr><td><strong>Laba Bersih</strong></td><td class="amount"><strong>Rp {{ number_format($profitLoss['net_profit_amount'], 2, ',', '.') }}</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Neraca (Aggregate Table)</h2>
        <table>
            <tr><th>Pos</th><th>Nilai</th></tr>
            <tr><td>Kas &amp; Bank</td><td class="amount">Rp {{ number_format($balanceSheet['cash_balance'], 2, ',', '.') }}</td></tr>
            <tr><td>Piutang Usaha</td><td class="amount">Rp {{ number_format($balanceSheet['accounts_receivable_balance'], 2, ',', '.') }}</td></tr>
            <tr><td>Persediaan</td><td class="amount">Rp {{ number_format($balanceSheet['inventory_balance'], 2, ',', '.') }}</td></tr>
            <tr><td><strong>Total Aset</strong></td><td class="amount"><strong>Rp {{ number_format($balanceSheet['total_assets'], 2, ',', '.') }}</strong></td></tr>
            <tr><td>Utang Usaha</td><td class="amount">Rp {{ number_format($balanceSheet['accounts_payable_balance'], 2, ',', '.') }}</td></tr>
            <tr><td>Utang Gaji</td><td class="amount">Rp {{ number_format($balanceSheet['payroll_payable_balance'], 2, ',', '.') }}</td></tr>
            <tr><td><strong>Total Liabilitas</strong></td><td class="amount"><strong>Rp {{ number_format($balanceSheet['total_liabilities'], 2, ',', '.') }}</strong></td></tr>
            <tr><td>Ekuitas</td><td class="amount">Rp {{ number_format($balanceSheet['equity_balance'], 2, ',', '.') }}</td></tr>
            <tr><td>Laba Ditahan</td><td class="amount">Rp {{ number_format($balanceSheet['retained_earnings_balance'], 2, ',', '.') }}</td></tr>
            <tr><td><strong>Total Ekuitas</strong></td><td class="amount"><strong>Rp {{ number_format($balanceSheet['total_equity'], 2, ',', '.') }}</strong></td></tr>
            <tr><td>Selisih Neraca</td><td class="amount">Rp {{ number_format($balanceSheet['balance_delta'], 2, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Ringkasan Jurnal</h2>
        <table>
            <tr>
                <th>Jumlah Entry</th>
                <th>Jumlah Line</th>
                <th>Total Debit</th>
                <th>Total Kredit</th>
            </tr>
            <tr>
                <td>{{ number_format($journalSummary['entries_count'], 0, ',', '.') }}</td>
                <td>{{ number_format($journalSummary['line_count'], 0, ',', '.') }}</td>
                <td class="amount">Rp {{ number_format($journalSummary['total_debit'], 2, ',', '.') }}</td>
                <td class="amount">Rp {{ number_format($journalSummary['total_credit'], 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Preview 20 Jurnal Terbaru</h2>
        <table>
            <tr>
                <th>No Jurnal</th>
                <th>Tanggal</th>
                <th>Referensi</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
            @foreach ($journalPreview as $entry)
                <tr>
                    <td>{{ $entry->entry_number }}</td>
                    <td>{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td>{{ strtoupper((string) $entry->reference_type) }}#{{ $entry->reference_id }}</td>
                    <td>{{ $entry->description }}</td>
                    <td class="amount">Rp {{ number_format((float) $entry->total_debit, 2, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format((float) $entry->total_credit, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
