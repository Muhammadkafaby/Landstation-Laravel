import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const foundationCards = [
    {
        title: 'Public Website',
        description: 'Landing page, promo, layanan, dan entry booking customer.',
    },
    {
        title: 'Admin Control',
        description: 'Master data, dashboard, unit monitoring, dan konfigurasi bisnis.',
    },
    {
        title: 'POS Kasir',
        description: 'Timer layanan, order cafe, merge bill, cash, dan QRIS manual.',
    },
];

export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                            Land Station
                        </p>
                        <h2 className="text-2xl font-bold leading-tight text-white">
                            Admin Dashboard Foundation
                        </h2>
                    </div>
                    <Link
                        href={route('pos.index')}
                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                    >
                        Buka POS
                    </Link>
                </div>
            }
        >
            <Head title="Admin Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="grid gap-6 lg:grid-cols-3">
                        {foundationCards.map((card) => (
                            <div
                                key={card.title}
                                className="overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-6 shadow-sm"
                            >
                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                    Foundation
                                </div>
                                <h3 className="mt-3 text-xl font-semibold text-white">
                                    {card.title}
                                </h3>
                                <p className="mt-3 text-sm leading-6 text-zinc-400">
                                    {card.description}
                                </p>
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 overflow-hidden rounded-3xl border border-white/10 bg-zinc-900/80 shadow-sm">
                        <div className="grid gap-8 p-6 lg:grid-cols-[1.15fr,0.85fr] lg:p-8">
                            <div>
                                <h3 className="text-xl font-semibold text-white">
                                    Fondasi aplikasi siap untuk sprint modul inti.
                                </h3>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-zinc-400">
                                    Tahap ini menyiapkan shell admin internal yang akan dipakai untuk dashboard statistik, manajemen layanan, pengaturan tarif, booking ops, dan monitoring unit real-time.
                                </p>
                            </div>

                            <div className="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                    Next Up
                                </div>
                                <ul className="mt-4 space-y-3 text-sm leading-6 text-zinc-100">
                                    <li>Master data layanan, unit, menu, dan payment.</li>
                                    <li>Booking availability dan status unit.</li>
                                    <li>Timer per menit dengan start-time pricing.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
