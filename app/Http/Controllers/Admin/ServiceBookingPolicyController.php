<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceBookingPolicyRequest;
use App\Http\Requests\Admin\UpdateServiceBookingPolicyRequest;
use App\Models\ServiceBookingPolicy;
use Illuminate\Http\RedirectResponse;

class ServiceBookingPolicyController extends Controller
{
    public function store(StoreServiceBookingPolicyRequest $request): RedirectResponse
    {
        ServiceBookingPolicy::query()->create($request->validated());

        return redirect()->route('management.services.index');
    }

    public function update(UpdateServiceBookingPolicyRequest $request, ServiceBookingPolicy $serviceBookingPolicy): RedirectResponse
    {
        $serviceBookingPolicy->update($request->validated());

        return redirect()->route('management.services.index');
    }
}
