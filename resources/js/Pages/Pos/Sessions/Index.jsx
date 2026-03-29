import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

function StartSessionForm({ serviceOptions, bookingOptions }) {
    const defaultService = serviceOptions[0] ?? null;
    const defaultUnit = defaultService?.units[0] ?? null;

    const { data, setData, post, processing, errors } = useForm({
        booking_id: '',
        service_id: defaultService?.id ?? '',
        service_unit_id: defaultUnit?.id ?? '',
        customer_name: '',
        customer_phone: '',
        customer_email: '',
    });

    const selectedService = serviceOptions.find((service) => service.id === Number(data.service_id)) ?? defaultService;
    const units = selectedService?.units ?? [];

    const submit = (event) => {
        event.preventDefault();

        post(route('pos.sessions.store'), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-5 rounded-3xl border border-white/10 bg-zinc-950/70 p-6">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label className="text-sm font-medium text-zinc-200">Booking terkait</label>
                    <select
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.booking_id || ''}
                        onChange={(event) => {
                            const bookingId = event.target.value;
                            const booking = bookingOptions.find((option) => option.id === Number(bookingId));

                            setData((current) => ({
                                ...current,
                                booking_id: bookingId === '' ? '' : Number(bookingId),
                                service_id: booking?.serviceId ?? current.service_id,
                                service_unit_id: booking?.unitId ?? current.service_unit_id,
                                customer_name: booking ? booking.customerName : current.customer_name,
                            }));
                        }}
                    >
                        <option value="">Walk-in / tanpa booking</option>
                        {bookingOptions.map((booking) => (
                            <option key={booking.id} value={booking.id}>
                                {booking.bookingCode} · {booking.customerName} · {booking.serviceName}
                            </option>
                        ))}
                    </select>
                    {errors.booking_id && <p className="mt-2 text-sm text-rose-400">{errors.booking_id}</p>}
                </div>

                <div>
                    <label className="text-sm font-medium text-zinc-200">Service</label>
                    <select
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_id}
                        onChange={(event) => {
                            const serviceId = Number(event.target.value);
                            const service = serviceOptions.find((item) => item.id === serviceId);

                            setData((current) => ({
                                ...current,
                                service_id: serviceId,
                                service_unit_id: service?.units[0]?.id ?? '',
                            }));
                        }}
                    >
                        {serviceOptions.map((service) => (
                            <option key={service.id} value={service.id}>
                                {service.name} ({service.code})
                            </option>
                        ))}
                    </select>
                    {errors.service_id && <p className="mt-2 text-sm text-rose-400">{errors.service_id}</p>}
                </div>

                <div>
                    <label className="text-sm font-medium text-zinc-200">Unit</label>
                    <select
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_unit_id || ''}
                        onChange={(event) => setData('service_unit_id', event.target.value === '' ? '' : Number(event.target.value))}
                    >
                        <option value="">Pilih unit</option>
                        {units.map((unit) => (
                            <option key={unit.id} value={unit.id}>
                                {unit.name} ({unit.code})
                            </option>
                        ))}
                    </select>
                    {errors.service_unit_id && <p className="mt-2 text-sm text-rose-400">{errors.service_unit_id}</p>}
                </div>

                <div>
                    <label className="text-sm font-medium text-zinc-200">Nama customer</label>
                    <input
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.customer_name}
                        onChange={(event) => setData('customer_name', event.target.value)}
                    />
                    {errors.customer_name && <p className="mt-2 text-sm text-rose-400">{errors.customer_name}</p>}
                </div>

                <div>
                    <label className="text-sm font-medium text-zinc-200">No. HP</label>
                    <input
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.customer_phone}
                        onChange={(event) => setData('customer_phone', event.target.value)}
                    />
                    {errors.customer_phone && <p className="mt-2 text-sm text-rose-400">{errors.customer_phone}</p>}
                </div>

                <div>
                    <label className="text-sm font-medium text-zinc-200">Email</label>
                    <input
                        type="email"
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.customer_email}
                        onChange={(event) => setData('customer_email', event.target.value)}
                    />
                    {errors.customer_email && <p className="mt-2 text-sm text-rose-400">{errors.customer_email}</p>}
                </div>
            </div>

            <button
                type="submit"
                disabled={processing || serviceOptions.length === 0}
                className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
            >
                Start session
            </button>
        </form>
    );
}

function StopSessionForm({ session }) {
    const { patch, processing, errors } = useForm({});

    const submit = (event) => {
        event.preventDefault();

        patch(route('pos.sessions.stop', session.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="mt-4 flex flex-col gap-2 md:flex-row md:items-center">
            <button
                type="submit"
                disabled={processing}
                className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
            >
                Stop session
            </button>
            {errors.service_session && <p className="text-sm text-rose-400">{errors.service_session}</p>}
        </form>
    );
}

export default function PosSessionsIndex({ serviceOptions, bookingOptions, activeSessions }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                        Kasir
                    </p>
                    <h1 className="text-2xl font-bold text-white">POS Session Control</h1>
                </div>
            }
        >
            <Head title="POS Sessions" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Start timed service sessions</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Kasir dapat memulai sesi walk-in atau booking-linked selama unit masih aman dipakai. Harga di-snapshot tepat saat sesi dimulai.
                        </p>
                    </div>

                    <StartSessionForm serviceOptions={serviceOptions} bookingOptions={bookingOptions} />

                    <section className="space-y-4">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Active sessions</h2>
                            <p className="mt-2 text-sm text-zinc-400">Daftar sesi aktif/paused yang sedang menahan unit operasional.</p>
                        </div>

                        <div className="grid gap-6">
                            {activeSessions.map((session) => (
                                <article key={session.id} className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                                {session.sessionCode}
                                            </p>
                                            <h3 className="mt-2 text-xl font-semibold text-white">
                                                {session.customerName} · {session.serviceName}
                                            </h3>
                                            <p className="mt-2 text-sm text-zinc-400">
                                                {session.serviceCode} · {session.unitName} ({session.unitCode})
                                            </p>
                                            {session.bookingCode && (
                                                <p className="mt-2 text-sm text-zinc-500">Booking: {session.bookingCode}</p>
                                            )}
                                        </div>

                                        <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                            {session.status}
                                        </div>
                                    </div>

                                    <div className="mt-4 grid gap-3 md:grid-cols-3">
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                            Customer phone: <span className="font-semibold text-white">{session.customerPhone || '-'}</span>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                            Started at: <span className="font-semibold text-white">{session.startedAt}</span>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                            Booking link: <span className="font-semibold text-white">{session.bookingCode || 'Walk-in'}</span>
                                        </div>
                                    </div>

                                    <StopSessionForm session={session} />
                                </article>
                            ))}

                            {activeSessions.length === 0 && (
                                <div className="rounded-3xl border border-dashed border-white/15 bg-white/5 p-6 text-sm text-zinc-400">
                                    Belum ada session aktif.
                                </div>
                            )}
                        </div>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
