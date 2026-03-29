<?php

namespace App\Services\Checkout;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceBuilder
{
    public function buildForSession(ServiceSession $serviceSession, User $createdBy): Invoice
    {
        return DB::transaction(function () use ($serviceSession, $createdBy): Invoice {
            $serviceSession = ServiceSession::query()
                ->with(['customer', 'booking', 'orders.items'])
                ->lockForUpdate()
                ->findOrFail($serviceSession->id);

            if (! in_array($serviceSession->status, [ServiceSession::STATUS_COMPLETED, ServiceSession::STATUS_CANCELLED], true)) {
                throw ValidationException::withMessages([
                    'service_session' => 'Only completed or cancelled sessions can be invoiced.',
                ]);
            }

            $invoice = Invoice::query()
                ->with(['lines', 'payments'])
                ->where('service_session_id', $serviceSession->id)
                ->lockForUpdate()
                ->first();

            if ($invoice !== null && in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_VOID], true)) {
                throw ValidationException::withMessages([
                    'invoice' => 'Paid or void invoices can not be rebuilt.',
                ]);
            }

            if ($invoice === null) {
                $invoice = Invoice::query()->create([
                    'invoice_code' => $this->generateInvoiceCode(),
                    'customer_id' => $serviceSession->customer_id,
                    'booking_id' => $serviceSession->booking_id,
                    'service_session_id' => $serviceSession->id,
                    'status' => Invoice::STATUS_OPEN,
                    'subtotal_rupiah' => 0,
                    'discount_rupiah' => 0,
                    'tax_rupiah' => 0,
                    'grand_total_rupiah' => 0,
                    'issued_at' => CarbonImmutable::now(),
                    'created_by_user_id' => $createdBy->id,
                ]);
            }

            if ($invoice->payments->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'invoice' => 'Invoices with payments can not be rebuilt automatically.',
                ]);
            }

            $invoice->lines()->delete();

            $serviceSubtotal = $this->serviceSessionSubtotal($serviceSession);

            $invoice->lines()->create([
                'line_type' => 'service_session',
                'reference_type' => ServiceSession::class,
                'reference_id' => $serviceSession->id,
                'description' => sprintf('%s session', $serviceSession->service?->name ?? 'Service'),
                'qty' => $serviceSession->billed_minutes,
                'unit_price_rupiah' => $serviceSession->billed_minutes > 0
                    ? (int) round($serviceSubtotal / max($serviceSession->billed_minutes, 1))
                    : $serviceSubtotal,
                'subtotal_rupiah' => $serviceSubtotal,
                'snapshot_json' => [
                    'session_code' => $serviceSession->session_code,
                    'billed_minutes' => $serviceSession->billed_minutes,
                    'pricing_snapshot' => $serviceSession->pricing_snapshot_json,
                ],
            ]);

            $serviceSession->orders
                ->whereIn('status', [Order::STATUS_SUBMITTED, Order::STATUS_COMPLETED])
                ->each(function (Order $order) use ($invoice): void {
                    $order->items->each(function (OrderItem $item) use ($invoice): void {
                        $snapshot = $item->item_snapshot_json ?? [];

                        $invoice->lines()->create([
                            'line_type' => 'order_item',
                            'reference_type' => OrderItem::class,
                            'reference_id' => $item->id,
                            'description' => $snapshot['name'] ?? 'Cafe item',
                            'qty' => $item->qty,
                            'unit_price_rupiah' => $item->unit_price_rupiah,
                            'subtotal_rupiah' => $item->subtotal_rupiah,
                            'snapshot_json' => [
                                'order_id' => $item->order_id,
                                'item_snapshot' => $snapshot,
                            ],
                        ]);
                    });
                });

            $subtotal = (int) $invoice->lines()->sum('subtotal_rupiah');

            $invoice->update([
                'customer_id' => $serviceSession->customer_id,
                'booking_id' => $serviceSession->booking_id,
                'service_session_id' => $serviceSession->id,
                'status' => Invoice::STATUS_OPEN,
                'subtotal_rupiah' => $subtotal,
                'discount_rupiah' => 0,
                'tax_rupiah' => 0,
                'grand_total_rupiah' => $subtotal,
                'issued_at' => $invoice->issued_at ?? CarbonImmutable::now(),
                'closed_at' => null,
                'created_by_user_id' => $invoice->created_by_user_id ?? $createdBy->id,
            ]);

            return $invoice->fresh(['lines']);
        });
    }

    protected function serviceSessionSubtotal(ServiceSession $serviceSession): int
    {
        $snapshot = $serviceSession->pricing_snapshot_json ?? [];
        $pricingModel = $snapshot['pricing_model'] ?? null;

        if ($pricingModel === 'flat') {
            return (int) ($snapshot['base_price_rupiah'] ?? 0);
        }

        $intervalMinutes = max((int) ($snapshot['billing_interval_minutes'] ?? 1), 1);
        $intervalPrice = (int) ($snapshot['price_per_interval_rupiah'] ?? 0);
        $basePrice = (int) ($snapshot['base_price_rupiah'] ?? 0);
        $minimumCharge = (int) ($snapshot['minimum_charge_rupiah'] ?? 0);

        $perMinuteRate = $intervalPrice / $intervalMinutes;
        $computed = (int) round($perMinuteRate * (int) $serviceSession->billed_minutes);
        $subtotal = $basePrice + $computed;

        return max($subtotal, $minimumCharge);
    }

    protected function generateInvoiceCode(): string
    {
        do {
            $invoiceCode = 'INV-'.Str::upper(Str::random(10));
        } while (Invoice::query()->where('invoice_code', $invoiceCode)->exists());

        return $invoiceCode;
    }
}
