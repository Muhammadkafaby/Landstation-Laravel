import { Head, Link } from '@inertiajs/react';

const summaryLabels = [
    { key: 'categories', label: 'Kategori aktif' },
    { key: 'services', label: 'Service aktif' },
    { key: 'units', label: 'Unit aktif' },
];

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

export default function ServicesIndex({ summary, categories }) {
    return (
        <>
            <Head title="Layanan" />

            <div className="min-h-screen bg-zinc-950 px-6 py-10 text-white lg:px-8">
                <div className="mx-auto max-w-6xl space-y-8">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                Public Website
                            </p>
                            <h1 className="mt-2 text-4xl font-black tracking-tight">
                                Struktur layanan modular Land Station
                            </h1>
                            <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                                Halaman layanan kini membaca katalog service aktif dari database, termasuk ringkasan unit, kesiapan pricing, dan indikasi kesiapan booking.
                            </p>
                        </div>
                        <Link
                            href={route('home')}
                            className="rounded-full border border-white/15 px-4 py-2 text-sm transition hover:bg-white/10"
                        >
                            Kembali
                        </Link>
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
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

                                    <div className="grid grid-cols-2 gap-3 text-sm text-zinc-300 sm:min-w-[12rem]">
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
                                    </div>
                                </div>

                                <div className="mt-6 space-y-4">
                                    {category.services.map((service) => (
                                        <article
                                            key={service.slug}
                                            className="rounded-2xl border border-white/10 bg-zinc-950/70 p-5"
                                        >
                                            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <h3 className="text-lg font-semibold text-white">
                                                        {service.name}
                                                    </h3>
                                                    <p className="mt-2 text-sm text-zinc-400">
                                                        {service.serviceType} · {service.billingType}
                                                    </p>
                                                </div>

                                                <div className="grid gap-2 text-xs font-semibold uppercase tracking-[0.18em]">
                                                    <div className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-emerald-300">
                                                        Weekday · {formatRupiah(service.weekdayPriceRupiah ?? service.startingPriceRupiah)}
                                                    </div>
                                                    <div className="rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-1 text-fuchsia-200">
                                                        Weekend · {formatRupiah(service.weekendPriceRupiah ?? service.startingPriceRupiah)}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="mt-4 grid gap-3 sm:grid-cols-3">
                                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                    Unit tersedia: <span className="font-semibold text-white">{service.unitsCount}</span>
                                                </div>
                                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                    Pricing rule: <span className="font-semibold text-white">{service.hasPricing ? 'Ready' : 'Pending'}</span>
                                                </div>
                                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                                                    Booking policy: <span className="font-semibold text-white">{service.hasBookingPolicy ? 'Ready' : 'Pending'}</span>
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
        </>
    );
}
