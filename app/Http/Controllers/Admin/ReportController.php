<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServiceSession;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'summary' => [
                'bookingsTotal' => Booking::query()->count(),
                'activeSessions' => ServiceSession::query()->whereIn('status', [
                    ServiceSession::STATUS_ACTIVE,
                    ServiceSession::STATUS_PAUSED,
                ])->count(),
                'completedSessions' => ServiceSession::query()->where('status', ServiceSession::STATUS_COMPLETED)->count(),
                'submittedOrders' => Order::query()->where('status', Order::STATUS_SUBMITTED)->count(),
                'completedOrders' => Order::query()->where('status', Order::STATUS_COMPLETED)->count(),
                'openInvoices' => Invoice::query()->where('status', Invoice::STATUS_OPEN)->count(),
                'paidInvoices' => Invoice::query()->where('status', Invoice::STATUS_PAID)->count(),
                'verifiedRevenueRupiah' => (int) Payment::query()
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->sum('amount_rupiah'),
            ],
            'bookingSummary' => [
                'pending' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
                'confirmed' => Booking::query()->where('status', Booking::STATUS_CONFIRMED)->count(),
                'checkedIn' => Booking::query()->where('status', Booking::STATUS_CHECKED_IN)->count(),
                'completed' => Booking::query()->where('status', Booking::STATUS_COMPLETED)->count(),
                'cancelled' => Booking::query()->where('status', Booking::STATUS_CANCELLED)->count(),
                'noShow' => Booking::query()->where('status', Booking::STATUS_NO_SHOW)->count(),
                'fulfilled' => Booking::query()->whereIn('status', [
                    Booking::STATUS_CHECKED_IN,
                    Booking::STATUS_COMPLETED,
                ])->count(),
            ],
            'paymentMethodSummary' => [
                'cashRupiah' => (int) Payment::query()
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->where('payment_method_code', PaymentMethod::CASH)
                    ->sum('amount_rupiah'),
                'qrisManualRupiah' => (int) Payment::query()
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->where('payment_method_code', PaymentMethod::QRIS_MANUAL)
                    ->sum('amount_rupiah'),
                'verifiedPaymentsCount' => Payment::query()->where('status', Payment::STATUS_VERIFIED)->count(),
            ],
            'invoiceSummary' => [
                'openTotalRupiah' => (int) Invoice::query()->where('status', Invoice::STATUS_OPEN)->sum('grand_total_rupiah'),
                'paidTotalRupiah' => (int) Invoice::query()->where('status', Invoice::STATUS_PAID)->sum('grand_total_rupiah'),
            ],
        ]);
    }
}
