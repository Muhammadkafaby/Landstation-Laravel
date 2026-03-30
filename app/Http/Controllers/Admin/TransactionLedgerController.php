<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class TransactionLedgerController extends Controller
{
    public function __invoke(): Response
    {
        $ledger = Invoice::query()
            ->with([
                'customer:id,name,phone,email',
                'booking:id,booking_code',
                'serviceSession:id,session_code',
                'lines:id,invoice_id,line_type,description,qty,unit_price_rupiah,subtotal_rupiah',
                'payments.paymentMethod:code,name',
                'payments.verifiedBy:id,name',
            ])
            ->orderByDesc('issued_at')
            ->orderByDesc('invoice_code')
            ->get()
            ->map(function (Invoice $invoice): array {
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
            })
            ->values();

        return Inertia::render('Admin/Reports/Transactions/Index', [
            'ledger' => $ledger,
        ]);
    }
}
