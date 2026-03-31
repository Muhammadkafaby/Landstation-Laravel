import ApplicationLogo from '@/Components/ApplicationLogo';
import BookingForm from '@/Components/Bookings/BookingForm';
import { Head, Link } from '@inertiajs/react';

function PublicNav() {
    return (
        <header className="street-shell rounded-[1.6rem] px-4 py-4 sm:px-5">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <Link href={route('home')} className="w-fit street-motion hover:opacity-90">
                    <ApplicationLogo className="text-white" />
                </Link>

                <nav className="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300 sm:text-sm sm:tracking-[0.18em]">
                    <Link
                        href={route('home')}
                        className="street-pill street-motion rounded-full px-3 py-2 hover:border-lime-200/40 hover:text-zinc-100"
                    >
                        Home
                    </Link>
                    <Link
                        href={route('services.index')}
                        className="street-pill street-motion rounded-full px-3 py-2 hover:border-cyan-200/40 hover:text-zinc-100"
                    >
                        Layanan
                    </Link>
                    <Link
                        href={route('bookings.create')}
                        className="street-pill street-motion rounded-full px-3 py-2 text-zinc-100"
                    >
                        Booking
                    </Link>
                </nav>
            </div>
        </header>
    );
}

export default function PublicBookingCreate({ serviceOptions, preferredServiceId }) {
    return (
        <>
            <Head title="Booking" />

            <div className="street-page pb-28 lg:pb-12">
                <div className="street-grid-overlay" />

                <div className="street-container flex min-h-screen flex-col gap-7 px-1 py-5 sm:py-7">
                    <PublicNav />

                    <main className="space-y-7">
                        <section className="street-shell rounded-[1.8rem] p-5 sm:p-7">
                            <p className="street-heading-chip">Public Booking</p>
                            <h1 className="mt-4 max-w-4xl text-4xl font-extrabold uppercase leading-[0.96] text-zinc-50 sm:text-5xl">
                                Pilih Waktu, Tap Unit Di Layout, Dan Tahan Slot 10 Menit
                            </h1>
                            <p className="mt-3 max-w-3xl text-sm leading-7 text-zinc-300 sm:text-base">
                                Form ini fokus ke kecepatan. Jika kamu datang dari halaman layanan, service akan langsung terpilih agar proses booking lebih singkat.
                            </p>

                            <div className="mt-5 flex flex-wrap gap-3">
                                <Link
                                    href={route('services.index')}
                                    className="street-cta-secondary street-motion rounded-full px-5 py-3 text-xs hover:-translate-y-0.5 sm:text-sm"
                                >
                                    Kembali Ke Layanan
                                </Link>
                            </div>
                        </section>

                        <BookingForm
                            preferredServiceId={preferredServiceId}
                            serviceOptions={serviceOptions}
                            routeName="bookings.store"
                            submitLabel="Kirim Booking"
                        />
                    </main>
                </div>

                <div className="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-zinc-950/90 px-4 py-3 backdrop-blur lg:hidden">
                    <div className="street-container flex items-center justify-between gap-3">
                        <p className="text-xs font-semibold uppercase tracking-[0.15em] text-zinc-400">Pastikan unit sudah dipilih</p>
                        <a href="#booking-submit" className="street-cta-primary rounded-full px-4 py-2 text-[11px]">
                            Kirim Booking
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}
