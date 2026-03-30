<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\StoreCheckoutPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServiceSession;
use App\Services\Checkout\InvoiceBuilder;
use App\Services\Payments\ManualPaymentVerifier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(ServiceSession $serviceSession, InvoiceBuilder $invoiceBuilder): Response
    {
        $invoice = $this->resolveInvoice($serviceSession, $invoiceBuilder);

        return Inertia::render('Pos/Checkout/Show', [
            'serviceSession' => [
                'id' => $serviceSession->id,
                'sessionCode' => $serviceSession->session_code,
                'status' => $serviceSession->status,
            ],
            'invoice' => [
                'id' => $invoice->id,
                'invoiceCode' => $invoice->invoice_code,
                'status' => $invoice->status,
                'customerName' => $invoice->customer?->name,
                'subtotalRupiah' => $invoice->subtotal_rupiah,
                'discountRupiah' => $invoice->discount_rupiah,
                'taxRupiah' => $invoice->tax_rupiah,
                'grandTotalRupiah' => $invoice->grand_total_rupiah,
                'issuedAt' => optional($invoice->issued_at)->toIso8601String(),
                'closedAt' => optional($invoice->closed_at)->toIso8601String(),
                'lines' => $invoice->lines->map(fn ($line) => [
                    'id' => $line->id,
                    'lineType' => $line->line_type,
                    'description' => $line->description,
                    'qty' => $line->qty,
                    'unitPriceRupiah' => $line->unit_price_rupiah,
                    'subtotalRupiah' => $line->subtotal_rupiah,
                    'snapshot' => $line->snapshot_json,
                ])->values(),
            ],
            'payments' => $invoice->payments->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'paymentMethodCode' => $payment->payment_method_code,
                'status' => $payment->status,
                'amountRupiah' => $payment->amount_rupiah,
                'referenceNumber' => $payment->reference_number,
                'paidAt' => optional($payment->paid_at)->toIso8601String(),
                'notes' => $payment->notes,
                'verifiedByName' => $payment->verifiedBy?->name,
            ])->values(),
            'paymentMethods' => PaymentMethod::query()
                ->where('is_active', true)
                ->whereIn('code', [PaymentMethod::CASH, PaymentMethod::QRIS_MANUAL])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (PaymentMethod $paymentMethod) => [
                    'code' => $paymentMethod->code,
                    'name' => $paymentMethod->name,
                    'channel' => $paymentMethod->channel,
                ])
                ->values(),
            'remainingBalanceRupiah' => $this->remainingBalanceRupiah($invoice),
        ]);
    }

    public function storePayment(
        StoreCheckoutPaymentRequest $request,
        ServiceSession $serviceSession,
        InvoiceBuilder $invoiceBuilder,
        ManualPaymentVerifier $manualPaymentVerifier,
    ): RedirectResponse {
        $invoice = $this->resolveInvoice($serviceSession, $invoiceBuilder);

        $manualPaymentVerifier->verify(
            $invoice,
            $request->validated('payment_method_code'),
            (int) $request->validated('amount_rupiah'),
            $request->user(),
            $request->validated('reference_number'),
            $request->validated('notes'),
        );

        return redirect()->route('pos.checkout.show', $serviceSession);
    }

    protected function resolveInvoice(ServiceSession $serviceSession, InvoiceBuilder $invoiceBuilder): Invoice
    {
        $invoice = Invoice::query()
            ->with(['customer', 'lines', 'payments.verifiedBy'])
            ->where('service_session_id', $serviceSession->id)
            ->first();

        if ($invoice === null) {
            return $invoiceBuilder->buildForSession($serviceSession, request()->user())
                ->load(['customer', 'lines', 'payments.verifiedBy']);
        }

        if (in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_OPEN], true) && $invoice->payments->isEmpty()) {
            return $invoiceBuilder->buildForSession($serviceSession, request()->user())
                ->load(['customer', 'lines', 'payments.verifiedBy']);
        }

        return $invoice;
    }

    protected function remainingBalanceRupiah(Invoice $invoice): int
    {
        $verifiedTotal = (int) $invoice->payments
            ->where('status', Payment::STATUS_VERIFIED)
            ->sum('amount_rupiah');

        return max((int) $invoice->grand_total_rupiah - $verifiedTotal, 0);
    }
}
