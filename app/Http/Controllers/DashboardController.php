<?php

namespace App\Http\Controllers;

use App\Helpers\MenuHelper;
use App\Services\RetailOperationsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        $page = MenuHelper::findPage('dashboard');

        abort_unless($page !== null, 404);

        return view('pages.dashboard.retail-overview', [
            'title' => $page['title'],
        ]);
    }

    public function show(RetailOperationsService $retailOperationsService, string $pageKey): View
    {
        $modulePage = $retailOperationsService->resolve($pageKey);

        if ($modulePage !== null) {
            return view($modulePage['view'], $modulePage['data']);
        }

        return $this->renderPage($pageKey);
    }

    private function renderPage(string $key): View
    {
        $page = MenuHelper::findPage($key);

        abort_unless($page !== null, 404);

        return view('pages.dashboard.workspace', [
            'title' => $page['title'],
            'pageKey' => $key,
        ]);
    }
}
