<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CustomerHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = $this->customerQuery($this->search($request))
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Customer $customer) => $this->mapCustomer($customer));

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'q' => $this->search($request),
            ],
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $customers = $this->mapCustomers($this->customerQuery($this->search($request))->get());

        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, [
            'name',
            'phone',
            'email',
            'bookings_count',
            'sessions_count',
            'orders_count',
            'invoices_count',
            'verified_payments_rupiah',
            'last_activity_at',
        ]);

        foreach ($customers as $customer) {
            fputcsv($stream, [
                $customer['name'],
                $customer['phone'],
                $customer['email'],
                $customer['bookingsCount'],
                $customer['sessionsCount'],
                $customer['ordersCount'],
                $customer['invoicesCount'],
                $customer['verifiedPaymentsRupiah'],
                $customer['lastActivityAt'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=customer-history.csv',
        ]);
    }

    public function show(Customer $customer): Response
    {
        $customer->load([
            'bookings' => fn ($query) => $query
                ->with(['service:id,name,code', 'unit:id,name,code'])
                ->orderByDesc('start_at'),
            'serviceSessions' => fn ($query) => $query
                ->with(['service:id,name,code', 'unit:id,name,code', 'booking:id,booking_code,status'])
                ->orderByDesc('started_at'),
            'orders' => fn ($query) => $query
                ->withCount('items')
                ->orderByDesc('ordered_at'),
            'invoices' => fn ($query) => $query
                ->with(['payments.paymentMethod:code,name', 'payments.verifiedBy:id,name'])
                ->orderByDesc('issued_at'),
        ]);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'notes' => $customer->notes,
                'bookingsCount' => $customer->bookings->count(),
                'sessionsCount' => $customer->serviceSessions->count(),
                'ordersCount' => $customer->orders->count(),
                'invoicesCount' => $customer->invoices->count(),
                'verifiedPaymentsRupiah' => $this->verifiedPaymentsRupiah($customer),
                'lastActivityAt' => $this->lastActivityAt($customer)?->toIso8601String(),
            ],
            'bookings' => $customer->bookings->map(fn ($booking) => [
                'id' => $booking->id,
                'bookingCode' => $booking->booking_code,
                'status' => $booking->status,
                'source' => $booking->booking_source,
                'serviceName' => $booking->service?->name,
                'serviceCode' => $booking->service?->code,
                'unitName' => $booking->unit?->name,
                'unitCode' => $booking->unit?->code,
                'startAt' => optional($booking->start_at)->toIso8601String(),
                'endAt' => optional($booking->end_at)->toIso8601String(),
            ])->values(),
            'sessions' => $customer->serviceSessions->map(fn ($serviceSession) => [
                'id' => $serviceSession->id,
                'sessionCode' => $serviceSession->session_code,
                'status' => $serviceSession->status,
                'serviceName' => $serviceSession->service?->name,
                'serviceCode' => $serviceSession->service?->code,
                'unitName' => $serviceSession->unit?->name,
                'unitCode' => $serviceSession->unit?->code,
                'bookingCode' => $serviceSession->booking?->booking_code,
                'billedMinutes' => $serviceSession->billed_minutes,
                'startedAt' => optional($serviceSession->started_at)->toIso8601String(),
                'endedAt' => optional($serviceSession->ended_at)->toIso8601String(),
            ])->values(),
            'orders' => $customer->orders->map(fn ($order) => [
                'id' => $order->id,
                'orderCode' => $order->order_code,
                'status' => $order->status,
                'itemsCount' => $order->items_count,
                'orderedAt' => optional($order->ordered_at)->toIso8601String(),
            ])->values(),
            'invoices' => $customer->invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoiceCode' => $invoice->invoice_code,
                'status' => $invoice->status,
                'grandTotalRupiah' => $invoice->grand_total_rupiah,
                'issuedAt' => optional($invoice->issued_at)->toIso8601String(),
                'closedAt' => optional($invoice->closed_at)->toIso8601String(),
                'payments' => $invoice->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'paymentMethodCode' => $payment->payment_method_code,
                    'paymentMethodName' => $payment->paymentMethod?->name,
                    'status' => $payment->status,
                    'amountRupiah' => $payment->amount_rupiah,
                    'referenceNumber' => $payment->reference_number,
                    'paidAt' => optional($payment->paid_at)->toIso8601String(),
                    'verifiedByName' => $payment->verifiedBy?->name,
                ])->values(),
            ])->values(),
        ]);
    }

    protected function verifiedPaymentsRupiah(Customer $customer): int
    {
        return (int) $customer->invoices
            ->flatMap->payments
            ->where('status', Payment::STATUS_VERIFIED)
            ->sum('amount_rupiah');
    }

    protected function customerQuery(string $search): Builder
    {
        return Customer::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($customerQuery) use ($search): void {
                    $customerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount(['bookings', 'serviceSessions', 'orders', 'invoices'])
            ->with([
                'bookings:id,customer_id,start_at,end_at,created_at,updated_at',
                'serviceSessions:id,customer_id,started_at,ended_at,created_at,updated_at',
                'orders:id,customer_id,ordered_at,created_at,updated_at',
                'invoices:id,customer_id,issued_at,closed_at,created_at,updated_at',
                'invoices.payments:id,invoice_id,status,amount_rupiah,paid_at,created_at,updated_at',
            ])
            ->orderBy('name');
    }

    protected function mapCustomers($customers)
    {
        return $customers
            ->map(fn (Customer $customer): array => $this->mapCustomer($customer))
            ->values();
    }

    protected function mapCustomer(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'bookingsCount' => $customer->bookings_count,
            'sessionsCount' => $customer->service_sessions_count,
            'ordersCount' => $customer->orders_count,
            'invoicesCount' => $customer->invoices_count,
            'verifiedPaymentsRupiah' => $this->verifiedPaymentsRupiah($customer),
            'lastActivityAt' => $this->lastActivityAt($customer)?->toIso8601String(),
        ];
    }

    protected function search(Request $request): string
    {
        return trim((string) $request->string('q')->toString());
    }

    protected function lastActivityAt(Customer $customer): ?CarbonInterface
    {
        return Collection::make()
            ->merge($customer->bookings->map(fn ($booking) => $booking->updated_at ?? $booking->end_at ?? $booking->start_at))
            ->merge($customer->serviceSessions->map(fn ($serviceSession) => $serviceSession->ended_at ?? $serviceSession->started_at ?? $serviceSession->updated_at))
            ->merge($customer->orders->map(fn ($order) => $order->ordered_at ?? $order->updated_at))
            ->merge($customer->invoices->map(fn ($invoice) => $invoice->closed_at ?? $invoice->issued_at ?? $invoice->updated_at))
            ->merge($customer->invoices->flatMap->payments->map(fn ($payment) => $payment->paid_at ?? $payment->updated_at))
            ->filter()
            ->sortDesc()
            ->first();
    }
}
