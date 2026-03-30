import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(amount);

export default function TransactionLedgerIndex({ ledger }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Reports</p>
                        <h1 className="text-2xl font-bold text-white">Transaction Ledger</h1>
                    </div>
                    <Link
                        href={route('reports.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Reports
                    </Link>
                </div>
            }
        >
            <Head title="Transaction Ledger" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Invoice-centric commercial ledger</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Ledger ini menampilkan invoice, line items, pembayaran, dan sisa saldo agar admin bisa melakukan drill-down transaksi tanpa membuka payload mentah.
                        </p>
                    </div>

                    <div className="grid gap-6">
                        {ledger.map((invoice) => (
                            <section key={invoice.id} className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{invoice.invoiceCode}</p>
                                        <h2 className="mt-2 text-xl font-semibold text-white">{invoice.customerName || 'Walk-in customer'}</h2>
                                        <p className="mt-2 text-sm text-zinc-400">Booking {invoice.bookingCode || '-'} · Session {invoice.sessionCode || '-'}</p>
                                    </div>
                                    <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                        {invoice.status}
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-4">
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Issued: <span className="font-semibold text-white">{invoice.issuedAt || '-'}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Total: <span className="font-semibold text-white">{formatRupiah(invoice.grandTotalRupiah)}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Verified paid: <span className="font-semibold text-white">{formatRupiah(invoice.verifiedPaidRupiah)}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Remaining: <span className="font-semibold text-white">{formatRupiah(invoice.remainingBalanceRupiah)}</span></div>
                                </div>

                                <div className="mt-6 grid gap-6 xl:grid-cols-2">
                                    <div className="space-y-3">
                                        <h3 className="text-lg font-semibold text-white">Invoice lines</h3>
                                        {invoice.lines.map((line) => (
                                            <article key={line.id} className="rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-sm text-zinc-300">
                                                <div className="font-semibold text-white">{line.description}</div>
                                                <div className="mt-2">{line.lineType} · Qty {line.qty}</div>
                                                <div className="mt-2">Unit {formatRupiah(line.unitPriceRupiah)} · Subtotal {formatRupiah(line.subtotalRupiah)}</div>
                                            </article>
                                        ))}
                                    </div>

                                    <div className="space-y-3">
                                        <h3 className="text-lg font-semibold text-white">Payments</h3>
                                        {invoice.payments.length === 0 && (
                                            <div className="rounded-2xl border border-dashed border-white/15 bg-zinc-950/70 p-4 text-sm text-zinc-400">
                                                Belum ada payment.
                                            </div>
                                        )}
                                        {invoice.payments.map((payment) => (
                                            <article key={payment.id} className="rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-sm text-zinc-300">
                                                <div className="font-semibold text-white">{payment.paymentMethodCode} · {payment.status}</div>
                                                <div className="mt-2">{formatRupiah(payment.amountRupiah)}</div>
                                                <div className="mt-2 text-zinc-500">{payment.paidAt || '-'} · {payment.verifiedByName || '-'}</div>
                                            </article>
                                        ))}
                                    </div>
                                </div>
                            </section>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
