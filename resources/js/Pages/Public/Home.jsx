import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';

const categoryTheme = {
    billiard: {
        label: 'Cue Arena',
        glow: 'shadow-[0_25px_70px_-40px_rgba(132,204,22,0.55)]',
    },
    cafe: {
        label: 'Pit Stop',
        glow: 'shadow-[0_25px_70px_-40px_rgba(251,191,36,0.5)]',
    },
    playstation: {
        label: 'Console Zone',
        glow: 'shadow-[0_25px_70px_-40px_rgba(56,189,248,0.55)]',
    },
    'rental-rc': {
        label: 'Track Deck',
        glow: 'shadow-[0_25px_70px_-40px_rgba(163,230,53,0.55)]',
    },
};

const promoItems = [
    {
        code: 'LIVE',
        title: 'PS Night Rush',
        schedule: 'Senin-Kamis 21:00-01:00',
        body: 'Diskon sesi malam untuk party rank push setelah jam kerja.',
    },
    {
        code: 'LIMITED',
        title: 'RC Time Attack',
        schedule: 'Sabtu 19:00',
        body: 'Leaderboard mingguan dengan hadiah voucher booking berikutnya.',
    },
    {
        code: 'WEEKEND',
        title: 'Squad Billiard Pack',
        schedule: 'Jumat-Minggu 16:00-22:00',
        body: 'Paket 4 pemain plus minuman untuk sesi mabar santai.',
    },
];

const summaryLabels = [
    { key: 'categories', label: 'Kategori Aktif' },
    { key: 'services', label: 'Service Aktif' },
    { key: 'units', label: 'Unit Aktif' },
];

const formatRupiah = (amount) => {
    if (!amount) {
        return 'Harga update di venue';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
};

function TopNav({ auth, canLogin, canRegister }) {
    return (
        <header className="street-shell rounded-[1.6rem] px-4 py-4 sm:px-5">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <Link href={route('home')} className="w-fit street-motion hover:opacity-90">
                    <ApplicationLogo className="text-white" />
                </Link>

                <nav className="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300 sm:text-sm sm:tracking-[0.18em]">
                    <Link
                        href={route('home')}
                        className="street-pill street-motion rounded-full px-3 py-2 text-zinc-100"
                    >
                        Home
                    </Link>
                    <Link
                        href={route('services.index')}
                        className="street-pill street-motion rounded-full px-3 py-2 hover:border-lime-200/40 hover:text-zinc-100"
                    >
                        Layanan
                    </Link>
                    <Link
                        href={route('bookings.create')}
                        className="street-pill street-motion rounded-full px-3 py-2 hover:border-cyan-200/40 hover:text-zinc-100"
                    >
                        Booking
                    </Link>

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
                                    Staff
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
    );
}

function CategoryGrid({ categories }) {
    return (
        <section className="grid gap-4 lg:grid-cols-2">
            {categories.map((category) => {
                const theme = categoryTheme[category.code] ?? categoryTheme.playstation;

                return (
                    <article
                        key={category.code}
                        className={`street-card street-motion rounded-[1.5rem] p-5 hover:-translate-y-0.5 ${theme.glow}`}
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="street-heading-chip">{theme.label}</p>
                                <h3 className="mt-3 text-2xl font-extrabold text-zinc-50">{category.name}</h3>
                            </div>
                            <div className="street-pill rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]">
                                {category.unitsCount} Unit
                            </div>
                        </div>

                        <div className="mt-4 grid gap-2 text-sm text-zinc-300 sm:grid-cols-2">
                            <p className="street-pill rounded-2xl px-3 py-2">{category.servicesCount} service aktif</p>
                            <p className="street-pill rounded-2xl px-3 py-2">Mulai {formatRupiah(category.featuredService?.startingPriceRupiah)}</p>
                        </div>

                        <div className="mt-5 flex flex-wrap items-center gap-3">
                            <p className="text-sm text-zinc-400">{category.featuredService?.name ?? 'Service segera dibuka'}</p>
                            <Link
                                href={route('bookings.create')}
                                className="street-cta-secondary street-motion ml-auto rounded-full px-4 py-2 text-xs hover:-translate-y-0.5"
                            >
                                Cek Slot
                            </Link>
                        </div>
                    </article>
                );
            })}
        </section>
    );
}

function PromoRail() {
    return (
        <section className="space-y-4">
            <div className="flex items-end justify-between gap-3">
                <div>
                    <p className="street-heading-chip">Event Dan Promo</p>
                    <h2 className="mt-3 text-3xl font-extrabold text-zinc-50">Minggu ini lagi ramai di Land Station</h2>
                </div>
            </div>

            <div className="grid gap-3 lg:grid-cols-3">
                {promoItems.map((promo) => (
                    <article key={promo.title} className="street-card street-motion rounded-[1.4rem] p-5 hover:-translate-y-0.5">
                        <div className="flex items-center justify-between gap-2">
                            <span className="street-pill-live rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.18em]">
                                {promo.code}
                            </span>
                            <span className="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">This Week</span>
                        </div>
                        <h3 className="mt-4 text-2xl font-extrabold text-zinc-100">{promo.title}</h3>
                        <p className="mt-2 text-sm font-semibold text-cyan-200/85">{promo.schedule}</p>
                        <p className="mt-3 text-sm leading-6 text-zinc-400">{promo.body}</p>
                    </article>
                ))}
            </div>
        </section>
    );
}

export default function Home({ auth, canLogin, canRegister, summary, categories }) {
    return (
        <>
            <Head title="Land Station" />

            <div className="street-page pb-28 lg:pb-12">
                <div className="street-grid-overlay" />

                <div className="street-container flex min-h-screen flex-col gap-7 px-1 py-5 sm:py-7">
                    <TopNav auth={auth} canLogin={canLogin} canRegister={canRegister} />

                    <main className="space-y-7">
                        <section className="street-shell rounded-[1.8rem] p-5 sm:p-7">
                            <div className="grid items-start gap-6 lg:grid-cols-[1.1fr,0.9fr]">
                                <div>
                                    <p className="street-heading-chip">Street-Tech Venue</p>
                                    <h1 className="mt-4 text-4xl font-extrabold uppercase leading-[0.92] tracking-[-0.04em] text-zinc-50 sm:text-5xl lg:text-6xl">
                                        Pilih Layanan Dulu,
                                        <br />
                                        Baru Booking
                                        <br />
                                        Dalam Hitungan Menit.
                                    </h1>
                                    <p className="mt-4 max-w-2xl text-base leading-7 text-zinc-300">
                                        Buat pemain PS dan RC yang ingin cepat. Scan layanan, pilih slot dari layout visual, dan hold unit 10 menit sambil tim venue konfirmasi.
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
                                            Booking Cepat
                                        </Link>
                                    </div>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                                    {summaryLabels.map((item) => (
                                        <div key={item.key} className="street-card rounded-[1.3rem] p-4">
                                            <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-500">{item.label}</p>
                                            <p className="mt-3 text-4xl font-extrabold text-zinc-100">{summary[item.key]}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </section>

                        <PromoRail />
                        <CategoryGrid categories={categories} />
                    </main>

                    <footer className="border-t border-white/10 pt-6 text-xs uppercase tracking-[0.16em] text-zinc-500 sm:text-sm">
                        Land Station - Booking-first venue for PS, RC, billiard, and cafe.
                    </footer>
                </div>

                <div className="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-zinc-950/90 px-4 py-3 backdrop-blur lg:hidden">
                    <div className="street-container flex items-center justify-between gap-3">
                        <p className="text-xs font-semibold uppercase tracking-[0.15em] text-zinc-400">Start from services</p>
                        <Link href={route('services.index')} className="street-cta-primary rounded-full px-4 py-2 text-[11px]">
                            Lihat Layanan
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
