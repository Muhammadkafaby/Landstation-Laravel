import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function formatCountdown(totalSeconds) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function ActionButton({ bookingId, status, label, className }) {
    const { patch, processing } = useForm({ status });

    return (
        <button
            type="button"
            disabled={processing}
            onClick={() =>
                patch(route('management.bookings.transition', bookingId), {
                    preserveScroll: true,
                })
            }
            className={className}
        >
            {label}
        </button>
    );
}

function BookingTransitionForm({ booking, transitionOptions }) {
    const { data, setData, patch, processing, errors } = useForm({
        status: booking.status,
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route('management.bookings.transition', booking.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
            <select
                className="rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                value={data.status}
                onChange={(event) => setData('status', event.target.value)}
            >
                {transitionOptions.map((status) => (
                    <option key={status} value={status}>
                        {status}
                    </option>
                ))}
            </select>
            <button
                type="submit"
                disabled={processing}
                className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
            >
                Update status
            </button>
            {errors.status ? <p className="text-sm text-rose-400">{errors.status}</p> : null}
        </form>
    );
}

function HeldQueueCard({ booking, now }) {
    const holdExpiresAt = booking.holdExpiresAt ? new Date(booking.holdExpiresAt) : null;
    const remainingSeconds = holdExpiresAt
        ? Math.max(0, Math.floor((holdExpiresAt.getTime() - now.getTime()) / 1000))
        : booking.remainingSeconds;

    return (
        <section className="rounded-[2rem] border border-emerald-400/20 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.22),_transparent_35%),linear-gradient(180deg,_rgba(24,24,27,0.94),_rgba(9,9,11,0.98))] p-5 shadow-[0_24px_70px_-40px_rgba(16,185,129,0.7)]">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-300">
                        {booking.bookingCode}
                    </p>
                    <h2 className="mt-2 text-xl font-black tracking-[0.03em] text-white">
                        {booking.customerName} · {booking.serviceName}
                    </h2>
                    <p className="mt-2 text-sm text-zinc-300">
                        {booking.unitName ? `${booking.unitName} (${booking.unitCode})` : 'Tanpa unit'} · {booking.customerPhone || '-'}
                    </p>
                    <p className="mt-1 text-xs uppercase tracking-[0.18em] text-zinc-500">
                        {booking.source} · start {booking.startAt}
                    </p>
                </div>

                <div className="flex flex-col items-start gap-3 lg:items-end">
                    <div className="rounded-full border border-emerald-300/40 bg-emerald-300/15 px-4 py-2 text-sm font-black tracking-[0.2em] text-emerald-200">
                        {formatCountdown(remainingSeconds)}
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <ActionButton
                            bookingId={booking.id}
                            status="confirmed"
                            label="Confirm"
                            className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
                        />
                        <ActionButton
                            bookingId={booking.id}
                            status="cancelled"
                            label="Cancel"
                            className="inline-flex rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10 disabled:opacity-50"
                        />
                    </div>
                </div>
            </div>
        </section>
    );
}

export default function AdminBookingsIndex({ bookings, heldQueue, serverNow, transitionOptions }) {
    const [currentTime, setCurrentTime] = useState(() => new Date(serverNow));

    useEffect(() => {
        setCurrentTime(new Date(serverNow));
    }, [serverNow]);

    useEffect(() => {
        const tick = window.setInterval(() => {
            setCurrentTime((value) => new Date(value.getTime() + 1000));
        }, 1000);

        return () => window.clearInterval(tick);
    }, []);

    useEffect(() => {
        const poll = window.setInterval(() => {
            router.reload({
                only: ['bookings', 'heldQueue', 'serverNow'],
                preserveScroll: true,
                preserveState: true,
            });
        }, 30000);

        return () => window.clearInterval(poll);
    }, []);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                            Booking Management
                        </p>
                        <h1 className="text-2xl font-bold text-white">Booking Lifecycle</h1>
                    </div>
                    <Link
                        href={route('management.bookings.create')}
                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                    >
                        Create Booking
                    </Link>
                </div>
            }
        >
            <Head title="Booking Management" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Antrian hold yang butuh aksi cepat</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Booking publik yang baru masuk akan di-hold selama 10 menit. Queue ini menampilkan slot yang masih actionable, lengkap dengan countdown dan tombol confirm atau cancel.
                        </p>
                    </div>

                    {heldQueue.length > 0 ? (
                        <div className="grid gap-4">
                            {heldQueue.map((booking) => (
                                <HeldQueueCard key={booking.id} booking={booking} now={currentTime} />
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-3xl border border-dashed border-white/15 bg-white/[0.03] px-6 py-8 text-sm text-zinc-400">
                            Tidak ada booking hold yang masih aktif saat ini.
                        </div>
                    )}

                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Daftar booking operasional</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Semua booking tetap terlihat di daftar ini, termasuk hold yang sudah expired, booking yang sudah confirmed, sampai status terminal.
                        </p>
                    </div>

                    <div className="grid gap-6">
                        {bookings.data.map((booking) => (
                            <section key={booking.id} className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                            {booking.bookingCode}
                                        </p>
                                        <h2 className="mt-2 text-xl font-semibold text-white">
                                            {booking.customerName} · {booking.serviceName}
                                        </h2>
                                        <p className="mt-2 text-sm text-zinc-400">
                                            {booking.serviceCode} · {booking.unitName ?? 'Tanpa unit'} · {booking.source}
                                        </p>
                                    </div>
                                    <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                        {booking.status}
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-4">
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Customer phone: <span className="font-semibold text-white">{booking.customerPhone || '-'}</span>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Start: <span className="font-semibold text-white">{booking.startAt}</span>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Session links: <span className="font-semibold text-white">{booking.serviceSessionsCount}</span>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Hold until: <span className="font-semibold text-white">{booking.holdExpiresAt ?? '-'}</span>
                                    </div>
                                </div>

                                <BookingTransitionForm booking={booking} transitionOptions={transitionOptions} />
                            </section>
                        ))}
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {bookings.links.map((link, index) => (
                            link.url ? (
                                <Link
                                    key={`${link.label}-${index}`}
                                    href={link.url}
                                    className={`rounded-full px-4 py-2 text-sm font-semibold transition ${link.active ? 'bg-emerald-400 text-zinc-950' : 'border border-white/15 text-white hover:bg-white/10'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={`${link.label}-${index}`}
                                    className="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-500"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
