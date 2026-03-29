import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';

const services = [
    {
        name: 'Rental PlayStation',
        description: 'Sesi main fleksibel per menit dengan dukungan booking unit.',
    },
    {
        name: 'Billiard',
        description: 'Reservasi meja dan operasional kasir dalam satu sistem.',
    },
    {
        name: 'Rental RC',
        description: 'Kelola unit RC, jadwal penggunaan, dan transaksi cepat.',
    },
    {
        name: 'Cafe',
        description: 'Menu cafe terhubung langsung ke POS dan satu bill customer.',
    },
];

const modules = [
    'Website customer dengan booking online',
    'Admin dashboard dengan monitoring unit real-time',
    'POS kasir untuk timer, order cafe, dan checkout',
    'Promo, bundling, dan WhatsApp follow-up',
];

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="Land Station" />

            <div className="min-h-screen bg-zinc-950 text-white">
                <div className="absolute inset-x-0 top-0 -z-10 h-[38rem] bg-[radial-gradient(circle_at_top,_rgba(120,119,198,0.24),_transparent_42%),radial-gradient(circle_at_right,_rgba(16,185,129,0.18),_transparent_25%)]" />

                <div className="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-8">
                    <header className="flex flex-col gap-6 rounded-3xl border border-white/10 bg-white/5 px-6 py-5 backdrop-blur lg:flex-row lg:items-center lg:justify-between">
                        <Link href="/" className="w-fit">
                            <ApplicationLogo className="text-white" />
                        </Link>

                        <nav className="flex flex-wrap items-center gap-3 text-sm font-medium text-zinc-200">
                            <a href="#services" className="rounded-full px-3 py-2 transition hover:bg-white/10">
                                Layanan
                            </a>
                            <a href="#modules" className="rounded-full px-3 py-2 transition hover:bg-white/10">
                                Sistem
                            </a>

                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-full bg-emerald-400 px-4 py-2 font-semibold text-zinc-950 transition hover:bg-emerald-300"
                                >
                                    Masuk Dashboard
                                </Link>
                            ) : (
                                <>
                                    {canLogin && (
                                        <Link
                                            href={route('login')}
                                            className="rounded-full border border-white/15 px-4 py-2 transition hover:bg-white/10"
                                        >
                                            Login Staff
                                        </Link>
                                    )}
                                    {canRegister && (
                                        <Link
                                            href={route('register')}
                                            className="rounded-full bg-white px-4 py-2 font-semibold text-zinc-950 transition hover:bg-zinc-200"
                                        >
                                            Register
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </header>

                    <main className="flex flex-1 flex-col justify-center py-10 lg:py-16">
                        <section className="grid items-center gap-12 lg:grid-cols-[1.1fr,0.9fr]">
                            <div>
                                <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-400/25 bg-emerald-400/10 px-4 py-2 text-sm font-medium text-emerald-300">
                                    Land Station Foundation
                                </div>
                                <h1 className="max-w-4xl text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">
                                    Sistem manajemen terpadu untuk gaming, billiard, RC, dan cafe.
                                </h1>
                                <p className="mt-6 max-w-2xl text-base leading-7 text-zinc-300 sm:text-lg">
                                    Fondasi aplikasi Land Station sudah siap untuk website customer, dashboard admin, dan POS kasir dalam satu platform web.
                                </p>

                                <div className="mt-8 flex flex-wrap gap-3">
                                    <span className="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-zinc-200">
                                        Booking online
                                    </span>
                                    <span className="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-zinc-200">
                                        Timer per menit
                                    </span>
                                    <span className="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-zinc-200">
                                        QRIS manual
                                    </span>
                                    <span className="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-zinc-200">
                                        Real-time unit status
                                    </span>
                                </div>
                            </div>

                            <div id="services" className="grid gap-4 sm:grid-cols-2">
                                {services.map((service) => (
                                    <div
                                        key={service.name}
                                        className="rounded-3xl border border-white/10 bg-zinc-900/80 p-5 shadow-2xl shadow-black/20"
                                    >
                                        <div className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                            Layanan
                                        </div>
                                        <h2 className="mt-3 text-xl font-semibold text-white">
                                            {service.name}
                                        </h2>
                                        <p className="mt-2 text-sm leading-6 text-zinc-400">
                                            {service.description}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section id="modules" className="mt-16 rounded-[2rem] border border-white/10 bg-white/5 p-6 sm:p-8">
                            <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                        Arah Sistem
                                    </p>
                                    <h2 className="mt-2 text-2xl font-bold text-white sm:text-3xl">
                                        Tiga area utama dalam satu fondasi aplikasi.
                                    </h2>
                                </div>
                                <p className="max-w-xl text-sm leading-6 text-zinc-400">
                                    Versi saat ini menyiapkan shell public, admin, dan POS agar sprint berikutnya tinggal fokus ke data, booking, billing, dan transaksi.
                                </p>
                            </div>

                            <div className="mt-8 grid gap-4 lg:grid-cols-2">
                                {modules.map((module) => (
                                    <div
                                        key={module}
                                        className="rounded-2xl border border-white/10 bg-zinc-950/70 px-5 py-4 text-sm text-zinc-200"
                                    >
                                        {module}
                                    </div>
                                ))}
                            </div>
                        </section>
                    </main>

                    <footer className="border-t border-white/10 pt-6 text-sm text-zinc-500">
                        Land Station · Gaming, Billiard, RC, Cafe
                    </footer>
                </div>
            </div>
        </>
    );
}
