<?php

namespace App\Livewire\Operations;

use App\Services\RetailOperationsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CategoryManagerPanel extends Component
{
    public function render(RetailOperationsService $retailOperationsService): View
    {
        return view('livewire.operations.category-manager-panel', $retailOperationsService->categoryDirectoryData());
    }
}

