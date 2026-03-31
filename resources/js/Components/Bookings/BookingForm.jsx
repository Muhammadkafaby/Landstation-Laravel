import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import UnitLayoutPicker from '@/Components/Bookings/UnitLayoutPicker';
import { useForm } from '@inertiajs/react';
import { startTransition } from 'react';

const OPENING_HOUR = 9;
const LATEST_END_HOUR = 23;

function formatDateTimeLocal(date) {
    const pad = (value) => String(value).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function calculateEndAt(startAt, durationMinutes) {
    if (!startAt) {
        return '';
    }

    const duration = Number(durationMinutes);

    if (!Number.isFinite(duration) || duration <= 0) {
        return '';
    }

    const startDate = new Date(startAt);

    if (Number.isNaN(startDate.getTime())) {
        return '';
    }

    const endDate = new Date(startDate.getTime() + (duration * 60 * 1000));

    return formatDateTimeLocal(endDate);
}

function buildDurationOptions(service) {
    const slotInterval = Number(service?.bookingPolicy?.slotIntervalMinutes);
    const minDuration = Number(service?.bookingPolicy?.minDurationMinutes);
    const maxDuration = Number(service?.bookingPolicy?.maxDurationMinutes);

    if (!Number.isFinite(slotInterval) || !Number.isFinite(minDuration) || !Number.isFinite(maxDuration) || slotInterval <= 0 || minDuration <= 0 || maxDuration < minDuration) {
        return [60, 120, 180, 240];
    }

    const options = [];

    for (let value = minDuration; value <= maxDuration; value += slotInterval) {
        options.push(value);
    }

    return options.length > 0 ? options : [minDuration];
}

function formatDurationLabel(minutes) {
    if (minutes % 60 === 0) {
        const hours = minutes / 60;

        return `${hours} Jam`;
    }

    return `${minutes} Menit`;
}

function isValidPublicStartWindow(startAtValue, durationMinutes, referenceDate) {
    if (!startAtValue) {
        return false;
    }

    const duration = Number(durationMinutes);

    if (!Number.isFinite(duration) || duration <= 0) {
        return false;
    }

    const startAt = new Date(startAtValue);

    if (Number.isNaN(startAt.getTime())) {
        return false;
    }

    const todayStart = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), referenceDate.getDate(), 0, 0, 0, 0);
    const dayAfterTomorrowStart = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), referenceDate.getDate() + 2, 0, 0, 0, 0);

    if (startAt < todayStart || startAt >= dayAfterTomorrowStart) {
        return false;
    }

    if (startAt < referenceDate) {
        return false;
    }

    const openingAt = new Date(startAt.getFullYear(), startAt.getMonth(), startAt.getDate(), OPENING_HOUR, 0, 0, 0);

    if (startAt < openingAt) {
        return false;
    }

    const maxEndAt = new Date(startAt.getFullYear(), startAt.getMonth(), startAt.getDate(), LATEST_END_HOUR, 0, 0, 0);
    const endAt = new Date(startAt.getTime() + (duration * 60 * 1000));

    return endAt <= maxEndAt;
}

function computePublicStartMin(referenceDate) {
    const openingToday = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), referenceDate.getDate(), OPENING_HOUR, 0, 0, 0);

    return referenceDate > openingToday ? referenceDate : openingToday;
}

function computePublicStartMax(referenceDate, durationMinutes) {
    const duration = Number(durationMinutes);
    const safeDuration = Number.isFinite(duration) && duration > 0 ? duration : 60;
    const tomorrowLatestEnd = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), referenceDate.getDate() + 1, LATEST_END_HOUR, 0, 0, 0);

    return new Date(tomorrowLatestEnd.getTime() - (safeDuration * 60 * 1000));
}

function resolveComputedEnd(startAt, durationMinutes, isPublicBookingFlow, referenceDate) {
    const endAt = calculateEndAt(startAt, durationMinutes);

    if (!endAt) {
        return '';
    }

    if (!isPublicBookingFlow) {
        return endAt;
    }

    return isValidPublicStartWindow(startAt, durationMinutes, referenceDate) ? endAt : '';
}

export default function BookingForm({ serviceOptions, preferredServiceId, routeName, submitLabel }) {
    const labelClass = 'text-sm font-semibold text-white';
    const isPublicBookingFlow = routeName === 'bookings.store';
    const preferredService = serviceOptions.find((service) => service.id === Number(preferredServiceId)) ?? null;
    const defaultService = preferredService ?? serviceOptions[0] ?? null;
    const defaultUnit = defaultService?.units[0] ?? null;
    const defaultDuration = buildDurationOptions(defaultService)[0] ?? 60;

    const { data, setData, post, processing, errors } = useForm({
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        service_id: defaultService?.id ?? '',
        service_unit_id: defaultUnit?.id ?? '',
        start_at: '',
        end_at: '',
        duration_minutes: defaultDuration,
        notes: '',
    });

    const selectedService = serviceOptions.find((service) => service.id === Number(data.service_id)) ?? defaultService;
    const selectedUnits = selectedService?.units ?? [];
    const selectedUnit = selectedUnits.find((unit) => unit.id === Number(data.service_unit_id)) ?? null;
    const durationOptions = buildDurationOptions(selectedService);
    const slotIntervalMinutes = Number(selectedService?.bookingPolicy?.slotIntervalMinutes) > 0
        ? Number(selectedService?.bookingPolicy?.slotIntervalMinutes)
        : 60;
    const now = new Date();
    const minStartAt = isPublicBookingFlow ? formatDateTimeLocal(computePublicStartMin(now)) : undefined;
    const maxStartAt = isPublicBookingFlow ? formatDateTimeLocal(computePublicStartMax(now, data.duration_minutes)) : undefined;
    const hasInvalidPublicStart = isPublicBookingFlow && data.start_at && !isValidPublicStartWindow(data.start_at, data.duration_minutes, now);

    const submit = (event) => {
        event.preventDefault();

        post(route(routeName), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="street-shell rounded-[1.8rem] p-5 sm:p-6">
            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                <div className="min-w-0 space-y-5">
                    <div className="street-card grid gap-4 rounded-[1.5rem] p-5 md:grid-cols-2">
                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-service`} value="Service" />
                            <select
                                id={`${routeName}-service`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.service_id}
                                onChange={(event) => {
                                    const serviceId = Number(event.target.value);
                                    const nextService = serviceOptions.find((service) => service.id === serviceId);
                                    const nextDuration = buildDurationOptions(nextService)[0] ?? 60;

                                    startTransition(() => {
                                        setData((current) => ({
                                            ...current,
                                            service_id: serviceId,
                                            service_unit_id: nextService?.units[0]?.id ?? '',
                                            duration_minutes: nextDuration,
                                            end_at: resolveComputedEnd(current.start_at, nextDuration, isPublicBookingFlow, now),
                                        }));
                                    });
                                }}
                            >
                                {serviceOptions.map((service) => (
                                    <option key={service.id} value={service.id}>
                                        {service.name} ({service.code})
                                    </option>
                                ))}
                            </select>
                            <InputError className="mt-2" message={errors.service_id} />
                        </div>

                        <div className="street-pill rounded-[1.2rem] px-4 py-4 text-sm text-zinc-300">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-lime-200">Unit Terpilih</p>
                            <p className="mt-2 text-lg font-semibold text-white">{selectedUnit ? selectedUnit.name : 'Belum memilih unit'}</p>
                            <p className="mt-1 text-xs text-zinc-400">
                                {selectedUnit
                                    ? `${selectedUnit.code}${selectedUnit.zone ? ` | ${selectedUnit.zone}` : ''}`
                                    : 'Pilih langsung dari denah visual di bawah.'}
                            </p>
                        </div>

                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-start-at`} value="Mulai" />
                            <TextInput
                                id={`${routeName}-start-at`}
                                type="datetime-local"
                                step={slotIntervalMinutes * 60}
                                min={minStartAt}
                                max={maxStartAt}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.start_at}
                                onChange={(event) => {
                                    const startAt = event.target.value;

                                    setData((current) => ({
                                        ...current,
                                        start_at: startAt,
                                        end_at: resolveComputedEnd(startAt, current.duration_minutes, isPublicBookingFlow, now),
                                    }));
                                }}
                                required
                            />
                            <p className={`mt-2 text-xs ${hasInvalidPublicStart ? 'text-rose-300' : 'text-zinc-400'}`}>
                                Waktu mulai hanya hari ini/besok, minimal pukul 09:00. Jam selesai maksimal pukul 23:00.
                            </p>
                            <InputError className="mt-2" message={errors.start_at} />
                        </div>

                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-duration`} value="Paket Durasi" />
                            <select
                                id={`${routeName}-duration`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.duration_minutes}
                                onChange={(event) => {
                                    const durationMinutes = Number(event.target.value);

                                    setData((current) => ({
                                        ...current,
                                        duration_minutes: durationMinutes,
                                        end_at: resolveComputedEnd(current.start_at, durationMinutes, isPublicBookingFlow, now),
                                    }));
                                }}
                            >
                                {durationOptions.map((duration) => (
                                    <option key={duration} value={duration}>
                                        {formatDurationLabel(duration)}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="md:col-span-2">
                            <InputLabel className={labelClass} htmlFor={`${routeName}-end-at`} value="Selesai (Otomatis)" />
                            <TextInput
                                id={`${routeName}-end-at`}
                                type="datetime-local"
                                step={slotIntervalMinutes * 60}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.end_at}
                                readOnly
                                required
                            />
                            <p className="mt-2 text-xs text-zinc-400">Waktu selesai otomatis dari waktu mulai + paket durasi, dan maksimal pukul 23:00.</p>
                            <InputError className="mt-2" message={errors.end_at} />
                        </div>
                    </div>

                    <UnitLayoutPicker
                        service={selectedService}
                        units={selectedUnits}
                        selectedUnitId={Number(data.service_unit_id) || null}
                        onSelect={(unitId) => setData('service_unit_id', unitId)}
                    />
                    <InputError className="mt-2" message={errors.service_unit_id} />
                </div>

                <aside className="street-card min-w-0 space-y-5 rounded-[1.5rem] p-5">
                    <div>
                        <p className="street-heading-chip">Data Pemesan</p>
                        <h2 className="mt-3 text-2xl font-extrabold text-zinc-100">Lengkapi detail booking</h2>
                        <p className="mt-2 text-sm leading-6 text-zinc-400">
                            Setelah terkirim, slot masuk status hold selama 10 menit sambil menunggu konfirmasi staff.
                        </p>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-customer-name`} value="Nama customer" />
                            <TextInput
                                id={`${routeName}-customer-name`}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.customer_name}
                                onChange={(event) => setData('customer_name', event.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.customer_name} />
                        </div>

                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-customer-phone`} value="No. HP / WhatsApp" />
                            <TextInput
                                id={`${routeName}-customer-phone`}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.customer_phone}
                                onChange={(event) => setData('customer_phone', event.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.customer_phone} />
                        </div>

                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-customer-email`} value="Email" />
                            <TextInput
                                id={`${routeName}-customer-email`}
                                type="email"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.customer_email}
                                onChange={(event) => setData('customer_email', event.target.value)}
                            />
                            <InputError className="mt-2" message={errors.customer_email} />
                        </div>

                        <div>
                            <InputLabel className={labelClass} htmlFor={`${routeName}-notes`} value="Catatan" />
                            <textarea
                                id={`${routeName}-notes`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                rows={4}
                                value={data.notes}
                                onChange={(event) => setData('notes', event.target.value)}
                            />
                            <InputError className="mt-2" message={errors.notes} />
                        </div>
                    </div>

                    <section className="street-pill rounded-[1.2rem] px-4 py-4 text-sm text-zinc-300">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-200">Ringkasan pilihan</p>
                        <p className="mt-2">Service: <span className="font-bold text-zinc-100">{selectedService?.name ?? '-'}</span></p>
                        <p className="mt-1">Unit: <span className="font-bold text-zinc-100">{selectedUnit?.name ?? '-'}</span></p>
                        <p className="mt-1">Paket: <span className="font-bold text-zinc-100">{formatDurationLabel(Number(data.duration_minutes) || defaultDuration)}</span></p>
                        <p className="mt-1 text-xs text-zinc-400">Hold berlaku 10 menit setelah submit.</p>
                    </section>

                    <PrimaryButton
                        id="booking-submit"
                        className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 disabled:cursor-not-allowed"
                        disabled={processing || serviceOptions.length === 0 || !data.service_unit_id || !data.end_at || hasInvalidPublicStart}
                    >
                        {submitLabel}
                    </PrimaryButton>
                </aside>
            </div>
        </form>
    );
}
