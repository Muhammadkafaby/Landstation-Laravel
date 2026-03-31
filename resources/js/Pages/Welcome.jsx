import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';

const services = [
    {
        name: 'Rental PlayStation',
        description: 'Booking unit cepat dari mobile dengan visual slot picker.',
    },
    {
        name: 'Billiard',
        description: 'Kelola meja, hold booking, dan checkout dalam satu arus.',
    },
    {
        name: 'Rental RC',
        description: 'Monitoring unit track dengan jadwal dan transaksi terintegrasi.',
    },
    {
        name: 'Cafe',
        description: 'Order menu langsung masuk POS untuk satu bill customer.',
    },
];

const modules = [
    'Website publik untuk discovery layanan dan booking',
    'Admin dashboard untuk kontrol layanan, unit, dan laporan',
    'POS staff untuk sesi timer, order cafe, dan checkout',
    'Booking hold 10 menit dengan konfirmasi staff',
];

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="Land Station" />

            <div className="street-page pb-12">
                <div className="street-grid-overlay" />

                <div className="street-container flex min-h-screen flex-col gap-7 px-1 py-5 sm:py-7">
                    <header className="street-shell rounded-[1.6rem] px-4 py-4 sm:px-5">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <Link href={route('home')} className="w-fit street-motion hover:opacity-90">
                                <ApplicationLogo className="text-white" />
                            </Link>

                            <nav className="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300 sm:text-sm sm:tracking-[0.18em]">
                                <a href="#services" className="street-pill street-motion rounded-full px-3 py-2 hover:border-lime-200/40 hover:text-zinc-100">
                                    Layanan
                                </a>
                                <a href="#modules" className="street-pill street-motion rounded-full px-3 py-2 hover:border-cyan-200/40 hover:text-zinc-100">
                                    Sistem
                                </a>

                                {auth.user ? (
                                    <Link
                                        href={route('dashboard')}
                                        className="street-cta-primary street-motion rounded-full px-4 py-2 hover:-translate-y-0.5"
                                    >
                                        Dashboard
                                    </Link>
                                ) : (
                                    <>
                                        {canLogin ? (
                                            <Link
                                                href={route('login')}
                                                className="street-pill street-motion rounded-full px-3 py-2 hover:bg-white/10"
                                            >
                                                Login Staff
                                            </Link>
                                        ) : null}
                                        {canRegister ? (
                                            <Link
                                                href={route('register')}
                                                className="street-cta-secondary street-motion rounded-full px-3 py-2 hover:-translate-y-0.5"
                                            >
                                                Register
                                            </Link>
                                        ) : null}
                                    </>
                                )}
                            </nav>
                        </div>
                    </header>

                    <main className="space-y-7">
                        <section className="street-shell rounded-[1.8rem] p-5 sm:p-7">
                            <div className="grid items-center gap-8 lg:grid-cols-[1.08fr,0.92fr]">
                                <div>
                                    <p className="street-heading-chip">Platform Overview</p>
                                    <h1 className="mt-4 max-w-4xl text-4xl font-extrabold uppercase leading-[0.94] tracking-[-0.03em] text-zinc-50 sm:text-5xl lg:text-6xl">
                                        Fondasi Sistem Terpadu Untuk Operasional Venue Modern.
                                    </h1>
                                    <p className="mt-4 max-w-2xl text-base leading-7 text-zinc-300">
                                        Land Station menggabungkan website publik, dashboard admin, dan POS staff ke satu alur operasional yang konsisten dan cepat.
                                    </p>

                                    <div className="mt-6 flex flex-wrap gap-3">
                                        <Link
                                            href={route('services.index')}
                                            className="street-cta-primary street-motion rounded-full px-5 py-3 text-xs hover:-translate-y-0.5 sm:text-sm"
                                        >
                                            Lihat Layanan
                                        </Link>
                                        <Link
                                            href={route('bookings.create')}
                                            className="street-cta-secondary street-motion rounded-full px-5 py-3 text-xs hover:-translate-y-0.5 sm:text-sm"
                                        >
                                            Coba Booking
                                        </Link>
                                    </div>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                    <div className="street-card rounded-[1.3rem] p-4">
                                        <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-500">Booking</p>
                                        <p className="mt-2 text-lg font-bold text-zinc-100">Visual unit picker + hold 10 menit</p>
                                    </div>
                                    <div className="street-card rounded-[1.3rem] p-4">
                                        <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-500">POS</p>
                                        <p className="mt-2 text-lg font-bold text-zinc-100">Timer session, order cafe, dan checkout cepat</p>
                                    </div>
                                    <div className="street-card rounded-[1.3rem] p-4 sm:col-span-2 lg:col-span-1">
                                        <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-500">Admin</p>
                                        <p className="mt-2 text-lg font-bold text-zinc-100">Kontrol layanan, unit, laporan, dan audit</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="services" className="space-y-4">
                            <div>
                                <p className="street-heading-chip">Layanan</p>
                                <h2 className="mt-3 text-3xl font-extrabold text-zinc-50">Empat lini layanan dalam satu platform</h2>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                {services.map((service) => (
                                    <article key={service.name} className="street-card street-motion rounded-[1.4rem] p-5 hover:-translate-y-0.5">
                                        <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-lime-200">Service</p>
                                        <h3 className="mt-3 text-2xl font-extrabold text-zinc-100">{service.name}</h3>
                                        <p className="mt-2 text-sm leading-6 text-zinc-400">{service.description}</p>
                                    </article>
                                ))}
                            </div>
                        </section>

                        <section id="modules" className="street-shell rounded-[1.8rem] p-5 sm:p-7">
                            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p className="street-heading-chip">Arsitektur Produk</p>
                                    <h2 className="mt-3 text-3xl font-extrabold text-zinc-50">Modul inti untuk operasional harian</h2>
                                </div>
                                <p className="max-w-xl text-sm leading-7 text-zinc-400">
                                    Setiap modul dirancang agar data booking, pembayaran, dan aktivitas staff tetap sinkron pada satu sumber kebenaran.
                                </p>
                            </div>

                            <div className="mt-6 grid gap-3 lg:grid-cols-2">
                                {modules.map((module) => (
                                    <div key={module} className="street-card rounded-[1.2rem] px-4 py-3 text-sm font-medium text-zinc-200">
                                        {module}
                                    </div>
                                ))}
                            </div>
                        </section>
                    </main>

                    <footer className="border-t border-white/10 pt-6 text-xs uppercase tracking-[0.16em] text-zinc-500 sm:text-sm">
                        Land Station - Gaming, Billiard, RC, Cafe
                    </footer>
                </div>
            </div>
        </>
    );
}
