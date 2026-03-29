import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

const queueCards = [
    {
        title: 'Kontrol Sesi',
        description: 'Start, pause, resume, dan stop session PS, billiard, atau RC.',
    },
    {
        title: 'Order Cafe',
        description: 'Semua pesanan cafe dikelola langsung dari kasir pada transaksi aktif.',
    },
    {
        title: 'Checkout',
        description: 'Gabungkan service dan cafe dalam satu invoice dengan cash atau QRIS manual.',
    },
];

export default function PosIndex() {
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
                        <h2 className="text-xl font-semibold text-white">
                            Area POS siap untuk sprint operasional.
                        </h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Halaman ini akan menjadi pusat transaksi walk-in, timer layanan, input menu cafe, preview checkout, dan verifikasi pembayaran manual untuk Land Station.
                        </p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
