import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';

const formatRupiah = (amount) => {
    if (!amount) {
        return 'Info harga menyusul';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
};

export default function Home({ auth, canLogin, canRegister, summary, categories }) {
    return (
        <>
            <Head title="Land Station" />

            <div className="min-h-screen bg-zinc-950 text-white">
                <div className="absolute inset-x-0 top-0 -z-10 h-[38rem] bg-[radial-gradient(circle_at_top,_rgba(120,119,198,0.24),_transparent_42%),radial-gradient(circle_at_right,_rgba(16,185,129,0.18),_transparent_25%)]" />

                <div className="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-8">
                    <header className="flex flex-col gap-6 rounded-3xl border border-white/10 bg-white/5 px-6 py-5 backdrop-blur lg:flex-row lg:items-center lg:justify-between">
                        <Link href={route('home')} className="w-fit">
                            <ApplicationLogo className="text-white" />
                        </Link>

                        <nav className="flex flex-wrap items-center gap-3 text-sm font-medium text-zinc-200">
                            <Link
                                href={route('services.index')}
                                className="rounded-full px-3 py-2 transition hover:bg-white/10"
                            >
                                Layanan
                            </Link>

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
                                    Homepage sekarang membaca fondasi layanan aktif dari database agar arah pengembangan website public, dashboard admin, POS, dan management tetap sinkron.
                                </p>

                                <div className="mt-8 grid max-w-2xl gap-3 sm:grid-cols-3">
                                    <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <div className="text-sm text-zinc-400">Kategori aktif</div>
                                        <div className="mt-1 text-2xl font-black text-white">
                                            {summary.categories}
                                        </div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <div className="text-sm text-zinc-400">Service aktif</div>
                                        <div className="mt-1 text-2xl font-black text-white">
                                            {summary.services}
                                        </div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                        <div className="text-sm text-zinc-400">Unit aktif</div>
                                        <div className="mt-1 text-2xl font-black text-white">
                                            {summary.units}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                {categories.map((category) => (
                                    <div
                                        key={category.code}
                                        className="rounded-3xl border border-white/10 bg-zinc-900/80 p-5 shadow-2xl shadow-black/20"
                                    >
                                        <div className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                            {category.code}
                                        </div>
                                        <h2 className="mt-3 text-xl font-semibold text-white">
                                            {category.name}
                                        </h2>
                                        <p className="mt-2 text-sm leading-6 text-zinc-400">
                                            {category.description}
                                        </p>
                                        <div className="mt-4 flex flex-wrap gap-2 text-xs text-zinc-300">
                                            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1">
                                                {category.servicesCount} service
                                            </span>
                                            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1">
                                                {category.unitsCount} unit
                                            </span>
                                            <span className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-emerald-300">
                                                {formatRupiah(category.featuredService?.startingPriceRupiah)}
                                            </span>
                                        </div>
                                        <p className="mt-4 text-sm leading-6 text-zinc-500">
                                            Highlight: {category.featuredService?.name ?? 'Segera hadir'}
                                        </p>
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
