<?php

namespace App\Livewire\Operations;

use App\Services\AuditTrailService;
use App\Services\RetailOperationsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AuditTrailPanel extends Component
{
    use WithPagination;

    public string $module = '';
    public string $action = '';
    public string $search = '';
    public string $date_from = '';
    public string $date_to = '';
    public string $user_id = '';
    public int $perPage = 25;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $queryString = [
        'module' => ['except' => ''],
        'action' => ['except' => ''],
        'search' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'user_id' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function updatedModule(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = min(max((int) $this->perPage, 10), 100);
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->module = '';
        $this->action = '';
        $this->search = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->user_id = '';
        $this->perPage = 25;
        $this->resetPage();
    }

    public function render(AuditTrailService $auditTrailService, RetailOperationsService $retailOperationsService): View
    {
        $panelData = $auditTrailService->panelData(
            filters: $this->filters(),
            perPage: $this->perPage,
        );

        return view('livewire.operations.audit-trail-panel', array_merge(
            $retailOperationsService->auditTrailPageData(),
            $panelData,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return [
            'module' => $this->module,
            'action' => $this->action,
            'search' => $this->search,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'user_id' => $this->user_id,
        ];
    }
}

