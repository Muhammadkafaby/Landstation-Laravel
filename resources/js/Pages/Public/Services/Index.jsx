import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link } from '@inertiajs/react';

const summaryLabels = [
    { key: 'categories', label: 'Kategori Aktif' },
    { key: 'services', label: 'Service Aktif' },
    { key: 'units', label: 'Unit Aktif' },
];

const serviceTypeLabels = {
    timed_unit: 'Sewa Unit',
    menu_only: 'Menu Only',
};

const billingTypeLabels = {
    per_minute: 'Per Jam',
    flat: 'Flat Rate',
};

const formatRupiah = (amount) => {
    if (!amount) {
        return 'Harga update venue';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
};

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
                        className="street-pill street-motion rounded-full px-3 py-2 text-zinc-100"
                    >
                        Layanan
                    </Link>
                    <Link
                        href={route('bookings.create')}
                        className="street-pill street-motion rounded-full px-3 py-2 hover:border-cyan-200/40 hover:text-zinc-100"
                    >
                        Booking
                    </Link>
                </nav>
            </div>
        </header>
    );
}

function ServiceAction({ service }) {
    const readyForBooking = service.hasBookingPolicy && service.unitsCount > 0;

    if (!readyForBooking) {
        return (
            <span className="street-pill rounded-full px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-zinc-400">
                Belum Ready
            </span>
        );
    }

    return (
        <Link
            href={route('bookings.create', { service: service.id })}
            className="street-cta-primary street-motion rounded-full px-4 py-2 text-xs hover:-translate-y-0.5"
        >
            Pilih Dan Booking
        </Link>
    );
}

export default function ServicesIndex({ summary, categories }) {
    return (
        <>
            <Head title="Layanan" />

            <div className="street-page pb-28 lg:pb-12">
                <div className="street-grid-overlay" />

                <div className="street-container flex min-h-screen flex-col gap-7 px-1 py-5 sm:py-7">
                    <PublicNav />

                    <main className="space-y-7">
                        <section className="street-shell rounded-[1.8rem] p-5 sm:p-7">
                            <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p className="street-heading-chip">Service Hub</p>
                                    <h1 className="mt-4 text-4xl font-extrabold uppercase leading-[0.96] text-zinc-50 sm:text-5xl">
                                        Pilih Arena Yang Mau Kamu Mainkan
                                    </h1>
                                    <p className="mt-3 max-w-3xl text-sm leading-7 text-zinc-300 sm:text-base">
                                        Semua layanan aktif tampil langsung dari katalog. Cek harga mulai, unit aktif, lalu lanjut booking dengan service yang sudah dipilih otomatis.
                                    </p>
                                </div>
                                <Link href={route('bookings.create')} className="street-cta-secondary street-motion rounded-full px-5 py-3 text-xs hover:-translate-y-0.5 sm:text-sm">
                                    Masuk Booking
                                </Link>
                            </div>

                            <div className="mt-6 grid gap-3 md:grid-cols-3">
                                {summaryLabels.map((item) => (
                                    <div key={item.key} className="street-card rounded-[1.2rem] p-4">
                                        <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-500">{item.label}</p>
                                        <p className="mt-3 text-4xl font-extrabold text-zinc-100">{summary[item.key]}</p>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="space-y-5">
                            {categories.map((category) => (
                                <article key={category.code} className="street-shell rounded-[1.7rem] p-5 sm:p-6">
                                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <p className="street-heading-chip">{category.code}</p>
                                            <h2 className="mt-3 text-3xl font-extrabold text-zinc-100">{category.name}</h2>
                                            <p className="mt-3 max-w-3xl text-sm leading-7 text-zinc-400">{category.description}</p>
                                        </div>

                                        <div className="grid grid-cols-2 gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-zinc-400 sm:text-sm">
                                            <div className="street-pill rounded-2xl px-4 py-3">
                                                Services
                                                <p className="mt-1 text-lg font-extrabold text-zinc-100">{category.services_count}</p>
                                            </div>
                                            <div className="street-pill rounded-2xl px-4 py-3">
                                                Units
                                                <p className="mt-1 text-lg font-extrabold text-zinc-100">{category.units_count}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-6 grid gap-3">
                                        {category.services.map((service) => (
                                            <section key={service.slug} className="street-card street-motion rounded-[1.3rem] p-4 hover:-translate-y-0.5 sm:p-5">
                                                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                                    <div>
                                                        <h3 className="text-2xl font-extrabold text-zinc-100">{service.name}</h3>
                                                        <p className="mt-2 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 sm:text-sm">
                                                            {serviceTypeLabels[service.serviceType] ?? service.serviceType} | {billingTypeLabels[service.billingType] ?? service.billingType}
                                                        </p>
                                                    </div>

                                                    <div className="grid gap-2 text-xs font-bold uppercase tracking-[0.14em]">
                                                        <p className="street-pill-live rounded-full px-3 py-1">Weekday {formatRupiah(service.weekdayPriceRupiah ?? service.startingPriceRupiah)}</p>
                                                        <p className="street-pill rounded-full px-3 py-1 text-cyan-200">Weekend {formatRupiah(service.weekendPriceRupiah ?? service.startingPriceRupiah)}</p>
                                                    </div>
                                                </div>

                                                <div className="mt-4 grid gap-2 text-sm sm:grid-cols-3">
                                                    <p className="street-pill rounded-2xl px-3 py-2 text-zinc-300">Unit tersedia: <span className="font-bold text-zinc-100">{service.unitsCount}</span></p>
                                                    <p className="street-pill rounded-2xl px-3 py-2 text-zinc-300">Pricing: <span className="font-bold text-zinc-100">{service.hasPricing ? 'Ready' : 'Pending'}</span></p>
                                                    <p className="street-pill rounded-2xl px-3 py-2 text-zinc-300">Booking policy: <span className="font-bold text-zinc-100">{service.hasBookingPolicy ? 'Ready' : 'Pending'}</span></p>
                                                </div>

                                                <div className="mt-5 flex items-center justify-end">
                                                    <ServiceAction service={service} />
                                                </div>
                                            </section>
                                        ))}
                                    </div>
                                </article>
                            ))}
                        </section>
                    </main>
                </div>

                <div className="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-zinc-950/90 px-4 py-3 backdrop-blur lg:hidden">
                    <div className="street-container flex items-center justify-between gap-3">
                        <p className="text-xs font-semibold uppercase tracking-[0.15em] text-zinc-400">Ready to reserve?</p>
                        <Link href={route('bookings.create')} className="street-cta-primary rounded-full px-4 py-2 text-[11px]">
                            Lanjut Booking
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
