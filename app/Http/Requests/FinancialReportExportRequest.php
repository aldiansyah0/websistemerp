<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialReportExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $now = CarbonImmutable::now('Asia/Jakarta');

        $this->merge([
            'format' => $this->input('format') ?: 'excel',
            'start_date' => $this->input('start_date') ?: $now->startOfMonth()->toDateString(),
            'end_date' => $this->input('end_date') ?: $now->toDateString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'format' => ['required', Rule::in(['excel', 'pdf'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
