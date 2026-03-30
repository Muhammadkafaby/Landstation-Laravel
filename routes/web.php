<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\CustomerHistoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceBookingPolicyController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ServicePricingRuleController;
use App\Http\Controllers\Admin\ServiceUnitController;
use App\Http\Controllers\Admin\TransactionLedgerController;
use App\Http\Controllers\Pos\CheckoutController as PosCheckoutController;
use App\Http\Controllers\Pos\DashboardController as PosDashboardController;
use App\Http\Controllers\Pos\OrderController as PosOrderController;
use App\Http\Controllers\Pos\SessionController as PosSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BookingController as PublicBookingController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ServiceController;
use App\Models\Permission;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/services', ServiceController::class)->name('services.index');

Route::get('/booking', [PublicBookingController::class, 'create'])
    ->middleware('throttle:public-bookings')
    ->name('bookings.create');
Route::post('/bookings', [PublicBookingController::class, 'store'])
    ->middleware('throttle:public-bookings')
    ->name('bookings.store');

Route::get('/dashboard', AdminDashboardController::class)
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('dashboard');

Route::get('/reports', ReportController::class)
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('reports.index');

Route::get('/reports/export', [ReportController::class, 'export'])
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('reports.export');

Route::middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->prefix('reports/customers')
    ->name('reports.customers.')
    ->group(function () {
        Route::get('/', [CustomerHistoryController::class, 'index'])->name('index');
        Route::get('/export', [CustomerHistoryController::class, 'export'])->name('export');
        Route::get('/{customer}', [CustomerHistoryController::class, 'show'])->name('show');
    });

Route::get('/reports/transactions', TransactionLedgerController::class)
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('reports.transactions.index');

Route::get('/reports/transactions/export', [TransactionLedgerController::class, 'export'])
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('reports.transactions.export');

Route::get('/reports/audit', [AuditLogController::class, 'index'])
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('reports.audit.index');

Route::get('/reports/audit/export', [AuditLogController::class, 'export'])
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_ADMIN])
    ->name('reports.audit.export');

Route::get('/management', ManagementController::class)
    ->middleware(['auth', 'staff', 'permission:'.Permission::MANAGE_MASTER_DATA])
    ->name('management.index');

Route::middleware(['auth', 'staff', 'permission:'.Permission::MANAGE_MASTER_DATA])
    ->prefix('management')
    ->name('management.')
    ->group(function () {
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::post('/service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
        Route::patch('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update'])->name('service-categories.update');
        Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
        Route::patch('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
        Route::post('/service-units', [ServiceUnitController::class, 'store'])->name('service-units.store');
        Route::patch('/service-units/{serviceUnit}', [ServiceUnitController::class, 'update'])->name('service-units.update');
        Route::post('/service-pricing-rules', [ServicePricingRuleController::class, 'store'])->name('service-pricing-rules.store');
        Route::patch('/service-pricing-rules/{servicePricingRule}', [ServicePricingRuleController::class, 'update'])->name('service-pricing-rules.update');
        Route::post('/service-booking-policies', [ServiceBookingPolicyController::class, 'store'])->name('service-booking-policies.store');
        Route::patch('/service-booking-policies/{serviceBookingPolicy}', [ServiceBookingPolicyController::class, 'update'])->name('service-booking-policies.update');
    });

Route::middleware(['auth', 'staff', 'permission:'.Permission::MANAGE_BOOKINGS])
    ->prefix('management/bookings')
    ->name('management.bookings.')
    ->group(function () {
        Route::get('/', [BookingManagementController::class, 'index'])->name('index');
        Route::get('/create', [AdminBookingController::class, 'create'])->name('create');
        Route::post('/', [AdminBookingController::class, 'store'])->name('store');
        Route::patch('/{booking}/transition', [BookingManagementController::class, 'transition'])->name('transition');
    });

Route::get('/pos', PosDashboardController::class)
    ->middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_POS])
    ->name('pos.index');

Route::middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_POS])
    ->prefix('pos/sessions')
    ->name('pos.sessions.')
    ->group(function () {
        Route::get('/', [PosSessionController::class, 'index'])->name('index');
        Route::post('/', [PosSessionController::class, 'store'])->name('store');
        Route::patch('/{serviceSession}/stop', [PosSessionController::class, 'stop'])->name('stop');
    });

Route::middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_POS])
    ->prefix('pos/orders')
    ->name('pos.orders.')
    ->group(function () {
        Route::get('/', [PosOrderController::class, 'index'])->name('index');
        Route::post('/', [PosOrderController::class, 'store'])->name('store');
    });

Route::middleware(['auth', 'staff', 'permission:'.Permission::ACCESS_POS])
    ->prefix('pos/checkout')
    ->name('pos.checkout.')
    ->group(function () {
        Route::get('/{serviceSession}', [PosCheckoutController::class, 'show'])->name('show');
        Route::post('/{serviceSession}/payments', [PosCheckoutController::class, 'storePayment'])->name('payments.store');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
