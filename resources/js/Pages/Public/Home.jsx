import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';

const categoryTheme = {
    billiard: {
        accent: 'from-emerald-300 via-teal-300 to-cyan-300',
        glow: 'shadow-[0_22px_60px_-28px_rgba(52,211,153,0.75)]',
        label: 'TABLE READY',
    },
    cafe: {
        accent: 'from-amber-300 via-orange-300 to-emerald-300',
        glow: 'shadow-[0_22px_60px_-28px_rgba(251,191,36,0.55)]',
        label: 'PIT STOP',
    },
    playstation: {
        accent: 'from-cyan-300 via-emerald-300 to-lime-300',
        glow: 'shadow-[0_22px_60px_-28px_rgba(34,211,238,0.65)]',
        label: 'ROOM OPEN',
    },
    'rental-rc': {
        accent: 'from-lime-300 via-emerald-300 to-cyan-300',
        glow: 'shadow-[0_22px_60px_-28px_rgba(132,204,22,0.65)]',
        label: 'TRACK READY',
    },
};

const formatRupiah = (amount) => {
    if (! amount) {
        return 'Harga menyusul';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
};

function QuickBookingPanel({ categories }) {
    return (
        <div className="space-y-4 rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,_rgba(24,24,27,0.92),_rgba(6,10,18,0.98))] p-5">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-300">
                        Quick Booking
                    </p>
                    <h2 className="mt-2 text-2xl font-black tracking-[0.03em] text-white">
                        Masuk venue, pilih layanan, tahan slot.
                    </h2>
                </div>
                <div className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                    10 min hold
                </div>
            </div>

            <div className="grid gap-3">
                {categories.map((category) => {
                    const theme = categoryTheme[category.code] ?? categoryTheme.playstation;

                    return (
                        <div
                            key={category.code}
                            className={`rounded-[1.6rem] border border-white/10 bg-zinc-950/70 p-4 ${theme.glow}`}
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <p className={`inline-flex bg-gradient-to-r ${theme.accent} bg-clip-text text-[11px] font-black uppercase tracking-[0.26em] text-transparent`}>
                                        {theme.label}
                                    </p>
                                    <h3 className="mt-2 text-2xl font-black tracking-[0.02em] text-white">
                                        {category.name}
                                    </h3>
                                </div>
                                <div className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-2 text-sm font-semibold text-zinc-200">
                                    {category.unitsCount} unit
                                </div>
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">
                                <span className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1">
                                    {category.servicesCount} service
                                </span>
                                <span className={`rounded-full border border-white/10 bg-gradient-to-r ${theme.accent} px-3 py-1 text-zinc-950`}>
                                    {formatRupiah(category.featuredService?.startingPriceRupiah)}
                                </span>
                            </div>

                            <div className="mt-5 flex items-center justify-between gap-4">
                                <p className="text-sm text-zinc-400">
                                    {category.featuredService?.name ?? 'Segera aktif'}
                                </p>
                                <Link
                                    href={route('bookings.create')}
                                    className="inline-flex rounded-full border border-white/10 bg-white px-4 py-2 text-sm font-black text-zinc-950 transition hover:bg-zinc-200"
                                >
                                    Booking
                                </Link>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

function ProcessRail() {
    const steps = [
        {
            code: '01',
            title: 'Pilih layanan',
            body: 'Masuk ke booking publik dan tentukan PlayStation, billiard, atau RC.',
        },
        {
            code: '02',
            title: 'Pilih unit di denah',
            body: 'Kamu tidak lagi menebak nama unit. Posisi slot terlihat langsung di layout.',
        },
        {
            code: '03',
            title: 'Tahan slot 10 menit',
            body: 'Begitu submit, sistem menahan unit sambil staff melihat antrian konfirmasi.',
        },
        {
            code: '04',
            title: 'Masuk venue',
            body: 'Booking yang sudah dikonfirmasi tinggal dilanjutkan ke operasional di dashboard staff.',
        },
    ];

    return (
        <section className="grid gap-4 lg:grid-cols-4">
            {steps.map((step) => (
                <article
                    key={step.code}
                    className="rounded-[1.8rem] border border-white/10 bg-white/[0.04] p-5"
                >
                    <p className="text-[11px] font-black uppercase tracking-[0.28em] text-emerald-300">
                        {step.code}
                    </p>
                    <h3 className="mt-3 text-xl font-black tracking-[0.02em] text-white">
                        {step.title}
                    </h3>
                    <p className="mt-3 text-sm leading-6 text-zinc-400">
                        {step.body}
                    </p>
                </article>
            ))}
        </section>
    );
}

export default function Home({ auth, canLogin, canRegister, summary, categories }) {
    return (
        <>
            <Head title="Land Station" />

            <div className="min-h-screen overflow-hidden bg-[#05070b] text-white">
                <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.12),_transparent_26%),radial-gradient(circle_at_80%_20%,_rgba(16,185,129,0.14),_transparent_28%),linear-gradient(180deg,_rgba(255,255,255,0.04)_1px,_transparent_1px),linear-gradient(90deg,_rgba(255,255,255,0.04)_1px,_transparent_1px)] bg-[size:auto,auto,56px_56px,56px_56px]" />
                <div className="pointer-events-none absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.24),_transparent_40%)]" />

                <div className="relative mx-auto flex min-h-screen max-w-7xl flex-col px-5 py-6 lg:px-8">
                    <header className="flex flex-col gap-5 rounded-[2rem] border border-white/10 bg-zinc-950/70 px-6 py-5 backdrop-blur-xl lg:flex-row lg:items-center lg:justify-between">
                        <Link href={route('home')} className="w-fit">
                            <ApplicationLogo className="text-white" />
                        </Link>

                        <nav className="flex flex-wrap items-center gap-3 text-sm font-semibold text-zinc-200">
                            <Link
                                href={route('services.index')}
                                className="rounded-full border border-transparent px-3 py-2 transition hover:border-white/10 hover:bg-white/[0.05]"
                            >
                                Layanan
                            </Link>
                            <Link
                                href={route('bookings.create')}
                                className="rounded-full border border-emerald-300/25 bg-emerald-400/10 px-4 py-2 text-emerald-200 transition hover:bg-emerald-400/20"
                            >
                                Booking Sekarang
                            </Link>

                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-full bg-white px-5 py-2 font-black text-zinc-950 transition hover:bg-zinc-200"
                                >
                                    Masuk Dashboard
                                </Link>
                            ) : (
                                <>
                                    {canLogin ? (
                                        <Link
                                            href={route('login')}
                                            className="rounded-full border border-white/15 px-4 py-2 transition hover:bg-white/10"
                                        >
                                            Login Staff
                                        </Link>
                                    ) : null}
                                    {canRegister ? (
                                        <Link
                                            href={route('register')}
                                            className="rounded-full bg-white px-4 py-2 font-black text-zinc-950 transition hover:bg-zinc-200"
                                        >
                                            Register
                                        </Link>
                                    ) : null}
                                </>
                            )}
                        </nav>
                    </header>

                    <main className="flex flex-1 flex-col gap-10 py-10 lg:py-14">
                        <section className="grid items-start gap-8 lg:grid-cols-[1.08fr,0.92fr]">
                            <div className="space-y-8">
                                <div className="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-[11px] font-black uppercase tracking-[0.28em] text-emerald-300">
                                    Booking-first venue
                                </div>

                                <div className="space-y-5">
                                    <h1 className="max-w-5xl text-5xl font-black uppercase leading-[0.92] tracking-[-0.04em] text-white sm:text-6xl lg:text-7xl">
                                        Booking PS,
                                        <br />
                                        billiard, dan RC
                                        <br />
                                        tanpa ribet.
                                    </h1>
                                    <p className="max-w-2xl text-base leading-7 text-zinc-300 sm:text-lg">
                                        Masuk, pilih layanan, pilih unit di denah visual, lalu tahan slot selama 10 menit sambil staff mengonfirmasi. Website ini dirancang untuk pemain yang ingin booking cepat, bukan baca brosur panjang.
                                    </p>
                                </div>

                                <div className="flex flex-wrap items-center gap-3">
                                    <Link
                                        href={route('bookings.create')}
                                        className="inline-flex rounded-full bg-[linear-gradient(135deg,_#7fffd4,_#22d3ee)] px-6 py-3 text-sm font-black uppercase tracking-[0.18em] text-zinc-950 transition hover:scale-[1.02]"
                                    >
                                        Booking sekarang
                                    </Link>
                                    <Link
                                        href={route('services.index')}
                                        className="inline-flex rounded-full border border-white/15 px-6 py-3 text-sm font-black uppercase tracking-[0.18em] text-white transition hover:bg-white/10"
                                    >
                                        Lihat layanan
                                    </Link>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-3">
                                    <div className="rounded-[1.7rem] border border-white/10 bg-white/[0.04] px-4 py-4">
                                        <p className="text-xs uppercase tracking-[0.22em] text-zinc-500">Kategori aktif</p>
                                        <p className="mt-2 text-4xl font-black text-white">{summary.categories}</p>
                                    </div>
                                    <div className="rounded-[1.7rem] border border-white/10 bg-white/[0.04] px-4 py-4">
                                        <p className="text-xs uppercase tracking-[0.22em] text-zinc-500">Service aktif</p>
                                        <p className="mt-2 text-4xl font-black text-white">{summary.services}</p>
                                    </div>
                                    <div className="rounded-[1.7rem] border border-white/10 bg-white/[0.04] px-4 py-4">
                                        <p className="text-xs uppercase tracking-[0.22em] text-zinc-500">Unit aktif</p>
                                        <p className="mt-2 text-4xl font-black text-white">{summary.units}</p>
                                    </div>
                                </div>
                            </div>

                            <QuickBookingPanel categories={categories} />
                        </section>

                        <section className="space-y-5">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p className="text-[11px] font-black uppercase tracking-[0.28em] text-emerald-300">
                                        How it works
                                    </p>
                                    <h2 className="mt-2 text-3xl font-black uppercase tracking-[-0.03em] text-white">
                                        Masuk cepat, pilih slot, tahan unit.
                                    </h2>
                                </div>
                                <p className="max-w-xl text-sm leading-6 text-zinc-400">
                                    Alur publik sekarang fokus ke kecepatan. Semua langkah dibuat agar pemain bisa pindah dari homepage ke booking dalam satu arus yang jelas.
                                </p>
                            </div>

                            <ProcessRail />
                        </section>

                        <section className="rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,_rgba(24,24,27,0.92),_rgba(6,10,18,0.98))] p-6">
                            <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p className="text-[11px] font-black uppercase tracking-[0.28em] text-emerald-300">
                                        Venue signal
                                    </p>
                                    <h2 className="mt-2 text-3xl font-black uppercase tracking-[-0.03em] text-white">
                                        Bukan landing page biasa. Ini pintu masuk booking venue.
                                    </h2>
                                </div>
                                <div className="flex flex-wrap gap-3 text-sm font-semibold text-zinc-300">
                                    <span className="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2">
                                        Visual unit map
                                    </span>
                                    <span className="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2">
                                        Hold 10 menit
                                    </span>
                                    <span className="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2">
                                        Staff confirmation
                                    </span>
                                </div>
                            </div>
                        </section>
                    </main>

                    <footer className="border-t border-white/10 pt-6 text-sm text-zinc-500">
                        Land Station · Booking-first venue untuk PS, billiard, RC, dan cafe.
                    </footer>
                </div>
            </div>
        </>
    );
}
