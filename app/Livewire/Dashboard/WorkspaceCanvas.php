<?php

namespace App\Livewire\Dashboard;

use App\Helpers\MenuHelper;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WorkspaceCanvas extends Component
{
    public string $pageKey;

    public string $title;

    public string $pageTitle;

    public string $pageEyebrow;

    public string $pageDescription;

    public function mount(string $pageKey): void
    {
        $this->pageKey = $pageKey;
        $page = MenuHelper::findPage($pageKey);

        $this->title = $page['title'] ?? 'WebStellar ERP';
        $this->pageTitle = $page['title'] ?? 'WebStellar ERP';
        $this->pageEyebrow = $page['eyebrow'] ?? 'Workspace';
        $this->pageDescription = $page['description'] ?? 'Halaman ini disiapkan sebagai workspace ERP retail.';
    }

    public function render(): View
    {
        return view('livewire.dashboard.workspace-canvas');
    }
}
