import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(amount);

const summaryCards = [
    ['bookingsTotal', 'Bookings total'],
    ['activeSessions', 'Active sessions'],
    ['completedSessions', 'Completed sessions'],
    ['submittedOrders', 'Submitted orders'],
    ['completedOrders', 'Completed orders'],
    ['openInvoices', 'Open invoices'],
    ['paidInvoices', 'Paid invoices'],
];

const dateScopeOptions = [
    { value: 'all', label: 'Semua data' },
    { value: 'today', label: 'Hari ini' },
    { value: 'last_7_days', label: '7 hari terakhir' },
];

export default function ReportsIndex({ summary, bookingSummary, paymentMethodSummary, invoiceSummary, filters }) {
    const { data, setData, processing } = useForm({
        date_scope: filters.date_scope ?? 'all',
    });

    const submit = (event) => {
        event.preventDefault();

        router.get(route('reports.index'), {
            date_scope: data.date_scope,
        }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Reports</p>
                        <h1 className="text-2xl font-bold text-white">Operational Summaries</h1>
                    </div>
                    <Link
                        href={route('dashboard')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Dashboard
                    </Link>
                    <Link
                        href={route('reports.customers.index')}
                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                    >
                        Buka Customer History
                    </Link>
                    <Link
                        href={route('reports.transactions.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Buka Transaction Ledger
                    </Link>
                    <Link
                        href={route('reports.export', { date_scope: filters.date_scope })}
                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                    >
                        Export CSV
                    </Link>
                </div>
            }
        >
            <Head title="Reports" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="text-xl font-semibold text-white">Date scope</h2>
                                <p className="mt-2 text-sm text-zinc-400">Gunakan filter waktu untuk membatasi summary operasional dan komersial.</p>
                            </div>
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <select
                                    className="rounded-full border border-white/10 bg-zinc-950/70 px-4 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none focus:ring-0"
                                    value={data.date_scope}
                                    onChange={(event) => setData('date_scope', event.target.value)}
                                >
                                    {dateScopeOptions.map((option) => (
                                        <option key={option.value} value={option.value}>{option.label}</option>
                                    ))}
                                </select>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
                                >
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </form>

                    <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        {summaryCards.map(([key, label]) => (
                            <div key={key} className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div className="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">Summary</div>
                                <div className="mt-3 text-3xl font-black text-white">{summary[key]}</div>
                                <div className="mt-2 text-sm leading-6 text-zinc-300">{label}</div>
                            </div>
                        ))}
                    </div>

                    <div className="grid gap-6 xl:grid-cols-3">
                        <section className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6">
                            <h2 className="text-xl font-semibold text-white">Booking lifecycle</h2>
                            <div className="mt-4 grid gap-3">
                                {Object.entries(bookingSummary).map(([key, value]) => (
                                    <div key={key} className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                        {key}: <span className="font-semibold text-white">{value}</span>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6">
                            <h2 className="text-xl font-semibold text-white">Payment methods</h2>
                            <div className="mt-4 grid gap-3">
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Cash: <span className="font-semibold text-white">{formatRupiah(paymentMethodSummary.cashRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    QRIS manual: <span className="font-semibold text-white">{formatRupiah(paymentMethodSummary.qrisManualRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Verified payments: <span className="font-semibold text-white">{paymentMethodSummary.verifiedPaymentsCount}</span>
                                </div>
                            </div>
                        </section>

                        <section className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6">
                            <h2 className="text-xl font-semibold text-white">Invoice totals</h2>
                            <div className="mt-4 grid gap-3">
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Verified revenue: <span className="font-semibold text-white">{formatRupiah(summary.verifiedRevenueRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Open invoice total: <span className="font-semibold text-white">{formatRupiah(invoiceSummary.openTotalRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Paid invoice total: <span className="font-semibold text-white">{formatRupiah(invoiceSummary.paidTotalRupiah)}</span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
