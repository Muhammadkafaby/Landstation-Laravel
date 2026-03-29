import BookingForm from '@/Components/Bookings/BookingForm';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function AdminBookingCreate({ serviceOptions }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                            Booking Management
                        </p>
                        <h1 className="text-2xl font-bold text-white">Create Booking</h1>
                    </div>
                    <Link
                        href={route('management.bookings.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Booking List
                    </Link>
                </div>
            }
        >
            <Head title="Create Booking" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Internal booking entry</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Staff dapat membuat booking internal dengan validasi policy dan availability yang sama seperti flow public, tetapi source booking akan dicatat sebagai admin.
                        </p>
                    </div>

                    <BookingForm
                        serviceOptions={serviceOptions}
                        routeName="management.bookings.store"
                        submitLabel="Simpan booking"
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
