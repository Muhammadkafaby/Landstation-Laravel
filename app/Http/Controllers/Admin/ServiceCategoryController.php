<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Admin\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;

class ServiceCategoryController extends Controller
{
    public function store(StoreServiceCategoryRequest $request): RedirectResponse
    {
        ServiceCategory::query()->create($request->validated());

        return redirect()->route('management.services.index');
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $serviceCategory->update($request->validated());

        return redirect()->route('management.services.index');
    }
}
