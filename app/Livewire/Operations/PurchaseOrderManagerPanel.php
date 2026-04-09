<?php

namespace App\Livewire\Operations;

use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Services\RetailOperationsService;
use App\Workflows\PurchaseOrderWorkflow;
use DomainException;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PurchaseOrderManagerPanel extends Component
{
    public function submitPurchaseOrder(int $purchaseOrderId, PurchaseOrderWorkflow $workflow): void
    {
        $this->authorizeWorkflowAction();

        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        try {
            $workflow->submit($purchaseOrder);
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());

            return;
        }

        session()->flash('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil dikirim ke approval.');
    }

    public function approvePurchaseOrder(int $purchaseOrderId, PurchaseOrderWorkflow $workflow): void
    {
        $this->authorizeWorkflowAction();

        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        try {
            $workflow->approve($purchaseOrder);
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());

            return;
        }

        session()->flash('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil di-approve.');
    }

    public function rejectPurchaseOrder(int $purchaseOrderId, PurchaseOrderWorkflow $workflow): void
    {
        $this->authorizeWorkflowAction();

        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        try {
            $workflow->reject($purchaseOrder, 'Ditolak dari Livewire approval table');
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());

            return;
        }

        session()->flash('success', 'Purchase order ' . $purchaseOrder->po_number . ' ditandai sebagai rejected.');
    }

    public function cancelPurchaseOrder(int $purchaseOrderId, PurchaseOrderWorkflow $workflow): void
    {
        $this->authorizeWorkflowAction();

        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        try {
            $workflow->cancel($purchaseOrder, 'Dibatalkan dari Livewire approval table');
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());

            return;
        }

        session()->flash('success', 'Purchase order ' . $purchaseOrder->po_number . ' berhasil dibatalkan.');
    }

    public function render(RetailOperationsService $retailOperationsService): View
    {
        return view('livewire.operations.purchase-order-manager-panel', $retailOperationsService->purchaseOrderIndexData());
    }

    private function authorizeWorkflowAction(): void
    {
        $user = auth()->user();

        if ($user === null) {
            if (app()->environment(['local', 'testing'])) {
                return;
            }

            abort(403, 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        if ($user->hasRole(Role::OWNER) || $user->hasPermission('procurement.purchase.manage')) {
            return;
        }

        abort(403, 'Akses ditolak. Role Anda tidak memiliki izin untuk proses ini.');
    }
}
