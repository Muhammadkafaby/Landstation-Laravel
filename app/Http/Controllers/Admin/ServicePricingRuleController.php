<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServicePricingRuleRequest;
use App\Http\Requests\Admin\UpdateServicePricingRuleRequest;
use App\Models\ServicePricingRule;
use Illuminate\Http\RedirectResponse;

class ServicePricingRuleController extends Controller
{
    public function store(StoreServicePricingRuleRequest $request): RedirectResponse
    {
        ServicePricingRule::query()->create($request->validated());

        return redirect()->route('management.services.index');
    }

    public function update(UpdateServicePricingRuleRequest $request, ServicePricingRule $servicePricingRule): RedirectResponse
    {
        $servicePricingRule->update($request->validated());

        return redirect()->route('management.services.index');
    }
}
