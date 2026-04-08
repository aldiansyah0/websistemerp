<?php

namespace App\Livewire\Operations;

use App\Services\RetailOperationsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SupplierManagerPanel extends Component
{
    public function render(RetailOperationsService $retailOperationsService): View
    {
        return view('livewire.operations.supplier-manager-panel', $retailOperationsService->supplierDirectoryData());
    }
}

