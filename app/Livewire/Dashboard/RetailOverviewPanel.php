<?php

namespace App\Livewire\Dashboard;

use App\Services\RetailDashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class RetailOverviewPanel extends Component
{
    public function render(RetailDashboardService $retailDashboardService): View
    {
        return view('livewire.dashboard.retail-overview-panel', $retailDashboardService->build());
    }
}
