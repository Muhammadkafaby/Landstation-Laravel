<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TransactionLedgerController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $ledger = $this->ledgerQuery($request)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Invoice $invoice) => $this->mapInvoice($invoice));

        return Inertia::render('Admin/Reports/Transactions/Index', [
            'ledger' => $ledger,
            'filters' => $this->filters($request),
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $rows = $this->mapLedger($this->ledgerQuery($request)->get());

        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, [
            'invoice_code',
            'status',
            'customer_name',
            'booking_code',
            'session_code',
            'issued_at',
            'closed_at',
            'grand_total_rupiah',
            'verified_paid_rupiah',
            'remaining_balance_rupiah',
        ]);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['invoiceCode'],
                $row['status'],
                $row['customerName'],
                $row['bookingCode'],
                $row['sessionCode'],
                $row['issuedAt'],
                $row['closedAt'],
                $row['grandTotalRupiah'],
                $row['verifiedPaidRupiah'],
                $row['remainingBalanceRupiah'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=transaction-ledger.csv',
        ]);
    }

    protected function ledgerQuery(Request $request): Builder
    {
        $filters = $this->filters($request);

        return Invoice::query()
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $query->where(function ($invoiceQuery) use ($filters): void {
                    $invoiceQuery
                        ->where('invoice_code', 'like', "%{$filters['q']}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($filters): void {
                            $customerQuery->where('name', 'like', "%{$filters['q']}%");
                        });
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['payment_method'] !== '', function ($query) use ($filters): void {
                $query->whereHas('payments', function ($paymentQuery) use ($filters): void {
                    $paymentQuery->where('payment_method_code', $filters['payment_method']);
                });
            })
            ->with([
                'customer:id,name,phone,email',
                'booking:id,booking_code',
                'serviceSession:id,session_code',
                'lines:id,invoice_id,line_type,description,qty,unit_price_rupiah,subtotal_rupiah',
                'payments.paymentMethod:code,name',
                'payments.verifiedBy:id,name',
            ])
            ->orderByDesc('issued_at')
            ->orderByDesc('invoice_code');
    }

    protected function mapLedger($invoices)
    {
        return $invoices
            ->map(fn (Invoice $invoice): array => $this->mapInvoice($invoice))
            ->values();
    }

    protected function mapInvoice(Invoice $invoice): array
    {
        $verifiedPaidRupiah = (int) $invoice->payments
            ->where('status', Payment::STATUS_VERIFIED)
            ->sum('amount_rupiah');

        return [
            'id' => $invoice->id,
            'invoiceCode' => $invoice->invoice_code,
            'status' => $invoice->status,
            'customerName' => $invoice->customer?->name,
            'bookingCode' => $invoice->booking?->booking_code,
            'sessionCode' => $invoice->serviceSession?->session_code,
            'issuedAt' => optional($invoice->issued_at)->toIso8601String(),
            'closedAt' => optional($invoice->closed_at)->toIso8601String(),
            'grandTotalRupiah' => $invoice->grand_total_rupiah,
            'verifiedPaidRupiah' => $verifiedPaidRupiah,
            'remainingBalanceRupiah' => max((int) $invoice->grand_total_rupiah - $verifiedPaidRupiah, 0),
            'lines' => $invoice->lines->map(fn ($line) => [
                'id' => $line->id,
                'lineType' => $line->line_type,
                'description' => $line->description,
                'qty' => $line->qty,
                'unitPriceRupiah' => $line->unit_price_rupiah,
                'subtotalRupiah' => $line->subtotal_rupiah,
            ])->values(),
            'payments' => $invoice->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'paymentMethodCode' => $payment->payment_method_code,
                'paymentMethodName' => $payment->paymentMethod?->name,
                'status' => $payment->status,
                'amountRupiah' => $payment->amount_rupiah,
                'paidAt' => optional($payment->paid_at)->toIso8601String(),
                'verifiedByName' => $payment->verifiedBy?->name,
            ])->values(),
        ];
    }

    protected function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->string('q')->toString()),
            'status' => trim((string) $request->string('status')->toString()),
            'payment_method' => trim((string) $request->string('payment_method')->toString()),
        ];
    }
}
