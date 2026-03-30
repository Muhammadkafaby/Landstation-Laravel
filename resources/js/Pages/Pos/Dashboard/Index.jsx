import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const queueCards = [
    {
        title: 'Kontrol Sesi',
        description: 'Start, pause, resume, dan stop service session PS, billiard, atau RC.',
    },
    {
        title: 'Order Cafe',
        description: 'Semua pesanan cafe dikelola dari kasir pada transaksi yang sedang aktif.',
    },
    {
        title: 'Checkout',
        description: 'Gabungkan service dan cafe dalam satu invoice dengan cash atau QRIS manual.',
    },
];

export default function PosDashboardIndex() {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                        Kasir
                    </p>
                    <h1 className="text-2xl font-bold text-white">POS Foundation</h1>
                </div>
            }
        >
            <Head title="POS" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-6 lg:grid-cols-3">
                        {queueCards.map((card) => (
                            <div
                                key={card.title}
                                className="rounded-3xl border border-white/10 bg-white/5 p-6"
                            >
                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                    Module
                                </div>
                                <h2 className="mt-3 text-xl font-semibold text-white">
                                    {card.title}
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-zinc-400">
                                    {card.description}
                                </p>
                            </div>
                        ))}
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="text-xl font-semibold text-white">Session control siap dipakai</h2>
                                <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                                    Mulai dari sini untuk membuka session PS, billiard, atau RC dengan validasi overlap dan snapshot harga saat start.
                                </p>
                            </div>
                            <Link
                                href={route('pos.sessions.index')}
                                className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                            >
                                Buka Session Control
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="text-xl font-semibold text-white">Cafe order siap dipakai</h2>
                                <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                                    Kasir bisa mulai membuat order cafe, mengaitkannya ke booking atau session aktif, lalu menyimpan item snapshot untuk fondasi checkout berikutnya.
                                </p>
                            </div>
                            <Link
                                href={route('pos.orders.index')}
                                className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                            >
                                Buka Cafe Orders
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="text-xl font-semibold text-white">Checkout backend siap dipakai</h2>
                                <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                                    Setelah session selesai dan cafe order tercatat, invoice bisa dipreview serta dibayar manual lewat flow checkout POS.
                                </p>
                            </div>
                            <Link
                                href={route('pos.sessions.index')}
                                className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                Pilih session selesai
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
