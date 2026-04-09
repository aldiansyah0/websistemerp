<?php

namespace App\Workflows;

use App\Models\PayrollRun;
use App\Services\PayrollService;

class PayrollWorkflow
{
    public function __construct(
        private readonly PayrollService $service,
    ) {
    }

    public function submit(PayrollRun $payrollRun): PayrollRun
    {
        return $this->service->submit($payrollRun);
    }

    public function approve(PayrollRun $payrollRun): PayrollRun
    {
        return $this->service->approve($payrollRun);
    }

    public function pay(PayrollRun $payrollRun): PayrollRun
    {
        return $this->service->pay($payrollRun);
    }
}
