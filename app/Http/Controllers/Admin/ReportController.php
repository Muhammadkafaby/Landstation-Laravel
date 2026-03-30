<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServiceSession;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $reportData = $this->reportData($request);

        return Inertia::render('Admin/Reports/Index', [
            'summary' => $reportData['summary'],
            'bookingSummary' => $reportData['bookingSummary'],
            'paymentMethodSummary' => $reportData['paymentMethodSummary'],
            'invoiceSummary' => $reportData['invoiceSummary'],
            'filters' => $reportData['filters'],
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $reportData = $this->reportData($request);
        $dateScope = $reportData['filters']['date_scope'];

        $rows = [
            ['summary', 'bookingsTotal', $reportData['summary']['bookingsTotal'], $dateScope],
            ['summary', 'activeSessions', $reportData['summary']['activeSessions'], $dateScope],
            ['summary', 'completedSessions', $reportData['summary']['completedSessions'], $dateScope],
            ['summary', 'submittedOrders', $reportData['summary']['submittedOrders'], $dateScope],
            ['summary', 'completedOrders', $reportData['summary']['completedOrders'], $dateScope],
            ['summary', 'openInvoices', $reportData['summary']['openInvoices'], $dateScope],
            ['summary', 'paidInvoices', $reportData['summary']['paidInvoices'], $dateScope],
            ['summary', 'verifiedRevenueRupiah', $reportData['summary']['verifiedRevenueRupiah'], $dateScope],
            ['bookingSummary', 'pending', $reportData['bookingSummary']['pending'], $dateScope],
            ['bookingSummary', 'confirmed', $reportData['bookingSummary']['confirmed'], $dateScope],
            ['bookingSummary', 'checkedIn', $reportData['bookingSummary']['checkedIn'], $dateScope],
            ['bookingSummary', 'completed', $reportData['bookingSummary']['completed'], $dateScope],
            ['bookingSummary', 'cancelled', $reportData['bookingSummary']['cancelled'], $dateScope],
            ['bookingSummary', 'noShow', $reportData['bookingSummary']['noShow'], $dateScope],
            ['bookingSummary', 'fulfilled', $reportData['bookingSummary']['fulfilled'], $dateScope],
            ['paymentMethodSummary', 'cashRupiah', $reportData['paymentMethodSummary']['cashRupiah'], $dateScope],
            ['paymentMethodSummary', 'qrisManualRupiah', $reportData['paymentMethodSummary']['qrisManualRupiah'], $dateScope],
            ['paymentMethodSummary', 'verifiedPaymentsCount', $reportData['paymentMethodSummary']['verifiedPaymentsCount'], $dateScope],
            ['invoiceSummary', 'openTotalRupiah', $reportData['invoiceSummary']['openTotalRupiah'], $dateScope],
            ['invoiceSummary', 'paidTotalRupiah', $reportData['invoiceSummary']['paidTotalRupiah'], $dateScope],
        ];

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['section', 'metric', 'value', 'date_scope']);

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=reports-summary.csv',
        ]);
    }

    protected function reportData(Request $request): array
    {
        $dateScope = trim((string) $request->string('date_scope')->toString());
        $dateScope = in_array($dateScope, ['today', 'last_7_days'], true) ? $dateScope : 'all';

        return [
            'summary' => [
                'bookingsTotal' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->count(),
                'activeSessions' => $this->applyDateScope(ServiceSession::query(), 'started_at', $dateScope)
                    ->whereIn('status', [
                        ServiceSession::STATUS_ACTIVE,
                        ServiceSession::STATUS_PAUSED,
                    ])->count(),
                'completedSessions' => $this->applyDateScope(ServiceSession::query(), 'started_at', $dateScope)
                    ->where('status', ServiceSession::STATUS_COMPLETED)
                    ->count(),
                'submittedOrders' => $this->applyDateScope(Order::query(), 'ordered_at', $dateScope)
                    ->where('status', Order::STATUS_SUBMITTED)
                    ->count(),
                'completedOrders' => $this->applyDateScope(Order::query(), 'ordered_at', $dateScope)
                    ->where('status', Order::STATUS_COMPLETED)
                    ->count(),
                'openInvoices' => $this->applyDateScope(Invoice::query(), 'issued_at', $dateScope)
                    ->where('status', Invoice::STATUS_OPEN)
                    ->count(),
                'paidInvoices' => $this->applyDateScope(Invoice::query(), 'issued_at', $dateScope)
                    ->where('status', Invoice::STATUS_PAID)
                    ->count(),
                'verifiedRevenueRupiah' => (int) $this->applyDateScope(Payment::query(), 'paid_at', $dateScope)
                    ->whereNotNull('paid_at')
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->sum('amount_rupiah'),
            ],
            'bookingSummary' => [
                'pending' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->where('status', Booking::STATUS_PENDING)->count(),
                'confirmed' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->where('status', Booking::STATUS_CONFIRMED)->count(),
                'checkedIn' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->where('status', Booking::STATUS_CHECKED_IN)->count(),
                'completed' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->where('status', Booking::STATUS_COMPLETED)->count(),
                'cancelled' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->where('status', Booking::STATUS_CANCELLED)->count(),
                'noShow' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)->where('status', Booking::STATUS_NO_SHOW)->count(),
                'fulfilled' => $this->applyDateScope(Booking::query(), 'start_at', $dateScope)
                    ->whereIn('status', [
                        Booking::STATUS_CHECKED_IN,
                        Booking::STATUS_COMPLETED,
                    ])->count(),
            ],
            'paymentMethodSummary' => [
                'cashRupiah' => (int) $this->applyDateScope(Payment::query(), 'paid_at', $dateScope)
                    ->whereNotNull('paid_at')
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->where('payment_method_code', PaymentMethod::CASH)
                    ->sum('amount_rupiah'),
                'qrisManualRupiah' => (int) $this->applyDateScope(Payment::query(), 'paid_at', $dateScope)
                    ->whereNotNull('paid_at')
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->where('payment_method_code', PaymentMethod::QRIS_MANUAL)
                    ->sum('amount_rupiah'),
                'verifiedPaymentsCount' => $this->applyDateScope(Payment::query(), 'paid_at', $dateScope)
                    ->whereNotNull('paid_at')
                    ->where('status', Payment::STATUS_VERIFIED)
                    ->count(),
            ],
            'invoiceSummary' => [
                'openTotalRupiah' => (int) $this->applyDateScope(Invoice::query(), 'issued_at', $dateScope)
                    ->where('status', Invoice::STATUS_OPEN)
                    ->sum('grand_total_rupiah'),
                'paidTotalRupiah' => (int) $this->applyDateScope(Invoice::query(), 'issued_at', $dateScope)
                    ->where('status', Invoice::STATUS_PAID)
                    ->sum('grand_total_rupiah'),
            ],
            'filters' => [
                'date_scope' => $dateScope,
            ],
        ];
    }

    protected function applyDateScope(Builder $query, string $column, string $dateScope): Builder
    {
        if ($dateScope === 'all') {
            return $query;
        }

        $now = CarbonImmutable::now();

        if ($dateScope === 'today') {
            return $query->whereBetween($column, [$now->startOfDay(), $now->endOfDay()]);
        }

        return $query->whereBetween($column, [$now->subDays(6)->startOfDay(), $now->endOfDay()]);
    }
}
