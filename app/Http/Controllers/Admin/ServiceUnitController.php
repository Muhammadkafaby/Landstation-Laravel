<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceUnitRequest;
use App\Http\Requests\Admin\UpdateServiceUnitRequest;
use App\Models\ServiceUnit;
use Illuminate\Http\RedirectResponse;

class ServiceUnitController extends Controller
{
    public function store(StoreServiceUnitRequest $request): RedirectResponse
    {
        ServiceUnit::query()->create($request->validated());

        return redirect()->route('management.services.index');
    }

    public function update(UpdateServiceUnitRequest $request, ServiceUnit $serviceUnit): RedirectResponse
    {
        $serviceUnit->update($request->validated());

        return redirect()->route('management.services.index');
    }
}
