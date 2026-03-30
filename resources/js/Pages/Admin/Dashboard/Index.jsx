import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const summaryLabels = [
    { key: 'categories', label: 'Kategori aktif' },
    { key: 'services', label: 'Service aktif' },
    { key: 'timedServices', label: 'Timed service' },
    { key: 'menuServices', label: 'Menu service' },
    { key: 'units', label: 'Unit aktif' },
    { key: 'bookableUnits', label: 'Unit bookable' },
    { key: 'pricingRules', label: 'Pricing rules' },
    { key: 'bookingPolicies', label: 'Booking policies' },
];

const formatRupiah = (amount) => {
    if (!amount) {
        return 'Harga menyusul';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
};

export default function AdminDashboardIndex({ summary, categories }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                            Land Station
                        </p>
                        <h2 className="text-2xl font-bold leading-tight text-white">
                            Admin Operational Overview
                        </h2>
                    </div>
                    <div className="flex flex-wrap gap-3">
                        <Link
                            href={route('management.index')}
                            className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Buka Management
                        </Link>
                        <Link
                            href={route('pos.index')}
                            className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                        >
                            Buka POS
                        </Link>
                        <Link
                            href={route('reports.index')}
                            className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Buka Reports
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Admin Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        {summaryLabels.map((item) => (
                            <div
                                key={item.key}
                                className="overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-6 shadow-sm"
                            >
                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                    Overview
                                </div>
                                <div className="mt-3 text-3xl font-black text-white">
                                    {summary[item.key]}
                                </div>
                                <div className="mt-2 text-sm leading-6 text-zinc-300">
                                    {item.label}
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h3 className="text-xl font-semibold text-white">
                            Fondasi operasional siap diteruskan ke booking, POS, dan CRUD
                        </h3>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Dashboard ini sekarang membaca ringkasan kategori, service, unit, pricing rule, dan booking policy dari database sehingga admin dapat memverifikasi fondasi fleksibel sebelum modul operasional berikutnya dibuka.
                        </p>
                    </div>

                    <div className="grid gap-6 xl:grid-cols-2">
                        {categories.map((category) => (
                            <section
                                key={category.code}
                                className="rounded-3xl border border-white/10 bg-white/5 p-6"
                            >
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                            {category.code}
                                        </p>
                                        <h3 className="mt-2 text-2xl font-bold text-white">
                                            {category.name}
                                        </h3>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 text-sm text-zinc-300 sm:min-w-[14rem]">
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Services</div>
                                            <div className="mt-1 text-lg font-semibold text-white">{category.servicesCount}</div>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Units</div>
                                            <div className="mt-1 text-lg font-semibold text-white">{category.unitsCount}</div>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Pricing</div>
                                            <div className="mt-1 text-lg font-semibold text-white">{category.pricingRulesCount}</div>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Policies</div>
                                            <div className="mt-1 text-lg font-semibold text-white">{category.bookingPoliciesCount}</div>
                                        </div>
                                    </div>
                                </div>

                                {category.featuredService && (
                                    <div className="mt-6 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
                                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                                    Featured service
                                                </div>
                                                <h4 className="mt-2 text-lg font-semibold text-white">
                                                    {category.featuredService.name}
                                                </h4>
                                                <p className="mt-2 text-sm text-zinc-400">
                                                    {category.featuredService.serviceType} · {category.featuredService.billingType}
                                                </p>
                                            </div>

                                            <div className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300">
                                                {formatRupiah(category.featuredService.startingPriceRupiah)}
                                            </div>
                                        </div>

                                        <div className="mt-4 grid gap-3 sm:grid-cols-3">
                                            <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                Unit count: <span className="font-semibold text-white">{category.featuredService.unitsCount}</span>
                                            </div>
                                            <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                Booking policy: <span className="font-semibold text-white">{category.featuredService.hasBookingPolicy ? 'Ready' : 'Pending'}</span>
                                            </div>
                                            <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                Pricing state: <span className="font-semibold text-white">{category.featuredService.startingPriceRupiah ? 'Ready' : 'Pending'}</span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </section>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
