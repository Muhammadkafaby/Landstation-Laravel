import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

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
                className="rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
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
            {errors.status && <p className="text-sm text-rose-400">{errors.status}</p>}
        </form>
    );
}

export default function AdminBookingsIndex({ bookings, transitionOptions }) {
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
                        <h2 className="text-xl font-semibold text-white">Daftar booking operasional</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Staff dengan hak manage-bookings dapat melihat booking aktif maupun terminal, lalu memindahkan statusnya sesuai lifecycle minimal yang sudah diamankan di backend.
                        </p>
                    </div>

                    <div className="grid gap-6">
                        {bookings.map((booking) => (
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

                                <div className="mt-4 grid gap-3 md:grid-cols-3">
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Customer phone: <span className="font-semibold text-white">{booking.customerPhone || '-'}</span>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Start: <span className="font-semibold text-white">{booking.startAt}</span>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        Session links: <span className="font-semibold text-white">{booking.serviceSessionsCount}</span>
                                    </div>
                                </div>

                                <BookingTransitionForm booking={booking} transitionOptions={transitionOptions} />
                            </section>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
