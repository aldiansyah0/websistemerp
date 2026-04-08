<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\AnalyticsCacheService;
use App\Services\RetailOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function create(RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.customer-form', $retailOperationsService->customerFormData());
    }

    public function store(CustomerRequest $request, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $customer = Customer::query()->create($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('customer-directory')->with('success', 'Customer ' . $customer->name . ' berhasil ditambahkan.');
    }

    public function edit(Customer $customer, RetailOperationsService $retailOperationsService): View
    {
        return view('pages.operations.customer-form', $retailOperationsService->customerFormData($customer));
    }

    public function update(CustomerRequest $request, Customer $customer, AnalyticsCacheService $analyticsCacheService): RedirectResponse
    {
        $customer->update($request->validated());
        $analyticsCacheService->invalidate();

        return redirect()->route('customer-directory')->with('success', 'Customer ' . $customer->name . ' berhasil diperbarui.');
    }
}
