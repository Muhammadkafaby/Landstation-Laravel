<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicePricingRule;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingCreator
{
    public function create(array $data, string $source, string $status, ?User $createdBy = null): Booking
    {
        return DB::transaction(function () use ($data, $source, $status, $createdBy): Booking {
            $service = Service::query()->findOrFail($data['service_id']);
            $unit = isset($data['service_unit_id']) && $data['service_unit_id'] !== null
                ? ServiceUnit::query()->lockForUpdate()->findOrFail($data['service_unit_id'])
                : null;
            $startAt = CarbonImmutable::parse($data['start_at']);
            $endAt = CarbonImmutable::parse($data['end_at']);

            $this->assertUnitStillAvailable($service, $unit, $startAt, $endAt);

            $customer = $this->resolveCustomer($data);
            $holdExpiresAt = $status === Booking::STATUS_HELD
                ? CarbonImmutable::now()->addMinutes(10)
                : null;
            $confirmedAt = $status === Booking::STATUS_CONFIRMED
                ? CarbonImmutable::now()
                : null;

            return Booking::query()->create([
                'booking_code' => $this->generateBookingCode(),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'service_unit_id' => $unit?->id,
                'status' => $status,
                'hold_expires_at' => $holdExpiresAt,
                'confirmed_at' => $confirmedAt,
                'booking_source' => $source,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'duration_minutes' => $startAt->diffInMinutes($endAt),
                'pricing_snapshot_json' => $this->pricingSnapshot($service, $unit, $startAt),
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => $createdBy?->id,
            ]);
        });
    }

    protected function assertUnitStillAvailable(
        Service $service,
        ?ServiceUnit $unit,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
    ): void {
        if ($unit === null) {
            return;
        }

        $hasBlockingBooking = Booking::query()
            ->where('service_id', $service->id)
            ->where('service_unit_id', $unit->id)
            ->where(function ($query): void {
                $query->where(function ($heldQuery): void {
                    $heldQuery->where('status', Booking::STATUS_HELD)
                        ->whereNotNull('hold_expires_at')
                        ->where('hold_expires_at', '>', now());
                })->orWhereIn('status', [
                    Booking::STATUS_PENDING,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CHECKED_IN,
                ]);
            })
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

        $hasBlockingSession = ServiceSession::query()
            ->where('service_id', $service->id)
            ->where('service_unit_id', $unit->id)
            ->whereIn('status', [
                ServiceSession::STATUS_ACTIVE,
                ServiceSession::STATUS_PAUSED,
            ])
            ->where('started_at', '<', $endAt)
            ->where(function ($query) use ($startAt): void {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>', $startAt);
            })
            ->exists();

        if ($hasBlockingBooking || $hasBlockingSession) {
            throw ValidationException::withMessages([
                'service_unit_id' => 'The selected unit is no longer available for that booking window.',
            ]);
        }
    }

    protected function resolveCustomer(array $data): Customer
    {
        $customer = Customer::query()
            ->when(
                filled($data['customer_phone'] ?? null),
                fn ($query) => $query->where('phone', $data['customer_phone'])
            )
            ->when(
                blank($data['customer_phone'] ?? null) && filled($data['customer_email'] ?? null),
                fn ($query) => $query->where('email', $data['customer_email'])
            )
            ->first();

        if ($customer === null) {
            return Customer::query()->create([
                'name' => $data['customer_name'],
                'phone' => $data['customer_phone'],
                'email' => $data['customer_email'] ?? null,
            ]);
        }

        $customer->update([
            'name' => $data['customer_name'],
            'phone' => $data['customer_phone'],
            'email' => $data['customer_email'] ?? null,
        ]);

        return $customer;
    }

    protected function pricingSnapshot(Service $service, ?ServiceUnit $unit, CarbonImmutable $startAt): ?array
    {
        $pricingRule = ServicePricingRule::query()
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->where(function ($query) use ($unit): void {
                if ($unit !== null) {
                    $query->where('service_unit_id', $unit->id)
                        ->orWhereNull('service_unit_id');

                    return;
                }

                $query->whereNull('service_unit_id');
            })
            ->where(function ($query) use ($startAt): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $startAt);
            })
            ->where(function ($query) use ($startAt): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $startAt);
            })
            ->orderByRaw('service_unit_id is not null desc')
            ->orderByDesc('priority')
            ->first();

        if ($pricingRule === null) {
            return null;
        }

        return [
            'pricing_rule_id' => $pricingRule->id,
            'pricing_model' => $pricingRule->pricing_model,
            'billing_interval_minutes' => $pricingRule->billing_interval_minutes,
            'base_price_rupiah' => $pricingRule->base_price_rupiah,
            'price_per_interval_rupiah' => $pricingRule->price_per_interval_rupiah,
            'minimum_charge_rupiah' => $pricingRule->minimum_charge_rupiah,
            'resolved_at' => $startAt->toIso8601String(),
        ];
    }

    protected function generateBookingCode(): string
    {
        do {
            $bookingCode = 'BK-'.Str::upper(Str::random(10));
        } while (Booking::query()->where('booking_code', $bookingCode)->exists());

        return $bookingCode;
    }
}
