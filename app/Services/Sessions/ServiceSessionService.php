<?php

namespace App\Services\Sessions;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicePricingRule;
use App\Models\ServiceSession;
use App\Models\ServiceUnit;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceSessionService
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    public function start(array $data, User $startedBy): ServiceSession
    {
        return DB::transaction(function () use ($data, $startedBy): ServiceSession {
            $service = Service::query()->lockForUpdate()->findOrFail($data['service_id']);
            $unit = ServiceUnit::query()->lockForUpdate()->findOrFail($data['service_unit_id']);
            $booking = isset($data['booking_id']) && $data['booking_id']
                ? Booking::query()->lockForUpdate()->with('customer')->findOrFail($data['booking_id'])
                : null;

            $hasBlockingSession = ServiceSession::query()
                ->where('service_unit_id', $unit->id)
                ->whereIn('status', [ServiceSession::STATUS_ACTIVE, ServiceSession::STATUS_PAUSED])
                ->lockForUpdate()
                ->exists();

            if ($hasBlockingSession) {
                throw ValidationException::withMessages([
                    'service_unit_id' => 'The selected unit already has an active service session.',
                ]);
            }

            $customer = $booking?->customer ?? $this->resolveWalkInCustomer($data);
            $startedAt = CarbonImmutable::now();

            $session = ServiceSession::query()->create([
                'session_code' => $this->generateSessionCode(),
                'service_id' => $service->id,
                'service_unit_id' => $unit->id,
                'customer_id' => $customer?->id,
                'booking_id' => $booking?->id,
                'status' => ServiceSession::STATUS_ACTIVE,
                'started_at' => $startedAt,
                'billed_minutes' => 0,
                'pricing_snapshot_json' => $this->pricingSnapshot($service, $unit, $startedAt),
                'started_by_user_id' => $startedBy->id,
            ]);

            if ($booking !== null && $booking->status === Booking::STATUS_CONFIRMED) {
                $booking->update([
                    'status' => Booking::STATUS_CHECKED_IN,
                ]);
            }

            $this->auditLogger->log($startedBy, 'service_session.started', $session, [
                'booking_id' => $booking?->id,
                'customer_id' => $customer?->id,
                'service_id' => $service->id,
                'service_unit_id' => $unit->id,
            ]);

            return $session;
        });
    }

    public function stop(ServiceSession $serviceSession, User $closedBy): ServiceSession
    {
        return DB::transaction(function () use ($serviceSession, $closedBy): ServiceSession {
            $serviceSession = ServiceSession::query()
                ->lockForUpdate()
                ->with('booking')
                ->findOrFail($serviceSession->id);

            if ($serviceSession->status !== ServiceSession::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'service_session' => 'Only active service sessions can be stopped.',
                ]);
            }

            $endedAt = CarbonImmutable::now();

            $serviceSession->update([
                'status' => ServiceSession::STATUS_COMPLETED,
                'ended_at' => $endedAt,
                'billed_minutes' => $serviceSession->started_at->diffInMinutes($endedAt),
                'closed_by_user_id' => $closedBy->id,
            ]);

            if ($serviceSession->booking !== null && $serviceSession->booking->status === Booking::STATUS_CHECKED_IN) {
                $serviceSession->booking->update([
                    'status' => Booking::STATUS_COMPLETED,
                ]);
            }

            $this->auditLogger->log($closedBy, 'service_session.stopped', $serviceSession, [
                'booking_id' => $serviceSession->booking_id,
                'service_id' => $serviceSession->service_id,
                'service_unit_id' => $serviceSession->service_unit_id,
                'billed_minutes' => $serviceSession->billed_minutes,
            ]);

            return $serviceSession->refresh();
        });
    }

    protected function resolveWalkInCustomer(array $data): ?Customer
    {
        if (blank($data['customer_phone'] ?? null) && blank($data['customer_email'] ?? null)) {
            return null;
        }

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

    protected function pricingSnapshot(Service $service, ServiceUnit $unit, CarbonImmutable $startedAt): ?array
    {
        $pricingRule = ServicePricingRule::query()
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->where(function ($query) use ($unit): void {
                $query->where('service_unit_id', $unit->id)
                    ->orWhereNull('service_unit_id');
            })
            ->where(function ($query) use ($startedAt): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $startedAt);
            })
            ->where(function ($query) use ($startedAt): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $startedAt);
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
            'resolved_at' => $startedAt->toIso8601String(),
        ];
    }

    protected function generateSessionCode(): string
    {
        do {
            $sessionCode = 'SS-'.Str::upper(Str::random(10));
        } while (ServiceSession::query()->where('session_code', $sessionCode)->exists());

        return $sessionCode;
    }
}
