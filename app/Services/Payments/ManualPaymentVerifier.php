<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualPaymentVerifier
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function verify(
        Invoice $invoice,
        string $paymentMethodCode,
        int $amountRupiah,
        User $verifiedBy,
        ?string $referenceNumber = null,
        ?string $notes = null,
        array $payload = [],
    ): Payment {
        return DB::transaction(function () use ($invoice, $paymentMethodCode, $amountRupiah, $verifiedBy, $referenceNumber, $notes, $payload): Payment {
            $invoice = Invoice::query()->with('payments')->lockForUpdate()->findOrFail($invoice->id);

            if (! $verifiedBy->hasPermission(Permission::MANAGE_PAYMENTS)) {
                throw ValidationException::withMessages([
                    'verified_by_user_id' => 'The selected user does not have permission to verify payments.',
                ]);
            }

            if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_VOID], true)) {
                throw ValidationException::withMessages([
                    'invoice' => 'Paid or void invoices can not accept new payments.',
                ]);
            }

            $paymentMethod = PaymentMethod::query()
                ->where('code', $paymentMethodCode)
                ->where('is_active', true)
                ->first();

            if ($paymentMethod === null || ! in_array($paymentMethod->code, [PaymentMethod::CASH, PaymentMethod::QRIS_MANUAL], true)) {
                throw ValidationException::withMessages([
                    'payment_method_code' => 'The selected payment method is not available for manual verification.',
                ]);
            }

            if ($amountRupiah <= 0) {
                throw ValidationException::withMessages([
                    'amount_rupiah' => 'Payment amount must be greater than zero.',
                ]);
            }

            $verifiedTotal = (int) $invoice->payments
                ->where('status', Payment::STATUS_VERIFIED)
                ->sum('amount_rupiah');
            $remaining = (int) $invoice->grand_total_rupiah - $verifiedTotal;

            if ($amountRupiah > $remaining) {
                throw ValidationException::withMessages([
                    'amount_rupiah' => 'Payment amount exceeds the remaining invoice balance.',
                ]);
            }

            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'payment_method_code' => $paymentMethod->code,
                'status' => Payment::STATUS_VERIFIED,
                'amount_rupiah' => $amountRupiah,
                'paid_at' => CarbonImmutable::now(),
                'reference_number' => $referenceNumber,
                'verified_by_user_id' => $verifiedBy->id,
                'notes' => $notes,
                'payload_json' => $payload,
            ]);

            if ($amountRupiah === $remaining) {
                $invoice->update([
                    'status' => Invoice::STATUS_PAID,
                    'closed_at' => CarbonImmutable::now(),
                ]);
            }

            $this->auditLogger->log($verifiedBy, 'payment.verified', $invoice, [
                'payment_id' => $payment->id,
                'payment_method_code' => $payment->payment_method_code,
                'amount_rupiah' => $payment->amount_rupiah,
                'invoice_status' => $invoice->fresh()->status,
            ]);

            return $payment;
        });
    }
}
