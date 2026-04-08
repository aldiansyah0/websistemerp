<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutletRequest;
use App\Models\Outlet;
use App\Services\AnalyticsCacheService;
use App\Services\RetailOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function create(RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.outlet-form', $retailOperationsService->outletFormData());
    }

    public function store(OutletRequest $request, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        Outlet::query()->create($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('outlet')->with('success', 'Outlet baru berhasil ditambahkan ke jaringan retail.');
    }

    public function edit(Outlet $outlet, RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.outlet-form', $retailOperationsService->outletFormData($outlet));
    }

    public function update(OutletRequest $request, Outlet $outlet, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $outlet->update($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('outlet')->with('success', 'Data outlet berhasil diperbarui.');
    }
}
