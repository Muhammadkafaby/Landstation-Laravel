import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const summaryLabels = [
    { key: 'categories', label: 'Kategori layanan' },
    { key: 'services', label: 'Service aktif fondasi' },
    { key: 'units', label: 'Unit fisik' },
    { key: 'pricingRules', label: 'Pricing rules' },
    { key: 'bookingPolicies', label: 'Booking policies' },
];

export default function ManagementIndex({ summary, categories }) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                        Management
                    </p>
                    <h1 className="text-2xl font-bold text-white">
                        Master Data Foundation
                    </h1>
                </div>
            }
        >
            <Head title="Management" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
                        {summaryLabels.map((item) => (
                            <div
                                key={item.key}
                                className="rounded-3xl border border-white/10 bg-white/5 p-6"
                            >
                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                    Summary
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
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="text-xl font-semibold text-white">
                                    Master data sudah bergerak ke fondasi data-driven
                                </h2>
                                <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                                    Halaman ini sekarang membaca kategori, service, unit, pricing rule, dan booking policy dari database untuk memvalidasi fondasi fleksibel Land Station sebelum CRUD dibuka.
                                </p>
                            </div>
                            <Link
                                href={route('management.services.index')}
                                className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                            >
                                Buka Service Catalog
                            </Link>
                        </div>
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
                                        <h2 className="mt-2 text-2xl font-bold text-white">
                                            {category.name}
                                        </h2>
                                        <p className="mt-3 text-sm leading-6 text-zinc-400">
                                            {category.description}
                                        </p>
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 text-sm text-zinc-300 sm:min-w-[14rem]">
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Services</div>
                                            <div className="mt-1 text-lg font-semibold text-white">
                                                {category.services_count}
                                            </div>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Units</div>
                                            <div className="mt-1 text-lg font-semibold text-white">
                                                {category.units_count}
                                            </div>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Pricing</div>
                                            <div className="mt-1 text-lg font-semibold text-white">
                                                {category.pricing_rules_count}
                                            </div>
                                        </div>
                                        <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3">
                                            <div className="text-zinc-500">Policies</div>
                                            <div className="mt-1 text-lg font-semibold text-white">
                                                {category.booking_policies_count}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-6 space-y-4">
                                    {category.services.map((service) => (
                                        <article
                                            key={service.code}
                                            className="rounded-2xl border border-white/10 bg-zinc-950/70 p-5"
                                        >
                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <h3 className="text-lg font-semibold text-white">
                                                        {service.name}
                                                    </h3>
                                                    <p className="mt-2 text-sm text-zinc-400">
                                                        {service.code} · {service.serviceType} · {service.billingType}
                                                    </p>
                                                </div>

                                                <span className="inline-flex rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-300">
                                                    {service.isActive ? 'Active' : 'Inactive'}
                                                </span>
                                            </div>

                                            <div className="mt-4 grid gap-3 sm:grid-cols-3">
                                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                    Unit count: <span className="font-semibold text-white">{service.units_count}</span>
                                                </div>
                                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                    Pricing rules: <span className="font-semibold text-white">{service.pricing_rules_count}</span>
                                                </div>
                                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                    Booking policy: <span className="font-semibold text-white">{service.has_booking_policy ? 'Yes' : 'No'}</span>
                                                </div>
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            </section>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
