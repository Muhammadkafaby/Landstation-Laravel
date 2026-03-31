import BookingForm from '@/Components/Bookings/BookingForm';
import { Head, Link } from '@inertiajs/react';

export default function PublicBookingCreate({ serviceOptions }) {
    return (
        <>
            <Head title="Booking" />

            <div className="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.2),_transparent_30%),linear-gradient(180deg,_rgba(9,9,11,1),_rgba(3,7,18,0.98))] px-6 py-10 text-white lg:px-8">
                <div className="mx-auto max-w-7xl space-y-8">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.26em] text-emerald-300">
                                Public Booking
                            </p>
                            <h1 className="mt-2 max-w-4xl text-4xl font-black tracking-tight md:text-5xl">
                                Pilih slot langsung dari denah visual Land Station
                            </h1>
                            <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-300/85">
                                Booking publik sekarang tidak lagi memilih unit dari dropdown biasa. Pilih waktu, lihat posisi unit di layout, lalu tahan slot selama 10 menit sambil menunggu konfirmasi staff.
                            </p>
                        </div>
                        <Link
                            href={route('services.index')}
                            className="inline-flex w-fit rounded-full border border-white/15 bg-white/[0.03] px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
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
