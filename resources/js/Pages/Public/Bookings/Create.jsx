import BookingForm from '@/Components/Bookings/BookingForm';
import { Head, Link } from '@inertiajs/react';

export default function PublicBookingCreate({ serviceOptions }) {
    return (
        <>
            <Head title="Booking" />

            <div className="min-h-screen bg-zinc-950 px-6 py-10 text-white lg:px-8">
                <div className="mx-auto max-w-6xl space-y-8">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                Public Booking
                            </p>
                            <h1 className="mt-2 text-4xl font-black tracking-tight">
                                Booking layanan Land Station
                            </h1>
                            <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                                Flow ini memakai validasi policy dan availability resolver di backend untuk memastikan slot dan unit yang dipilih benar-benar layak dipesan.
                            </p>
                        </div>
                        <Link
                            href={route('services.index')}
                            className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Kembali ke layanan
                        </Link>
                    </div>

                    <BookingForm
                        serviceOptions={serviceOptions}
                        routeName="bookings.store"
                        submitLabel="Kirim booking"
                    />
                </div>
            </div>
        </>
    );
}
