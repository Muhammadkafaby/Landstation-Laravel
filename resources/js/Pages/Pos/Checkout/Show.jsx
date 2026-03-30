import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(amount);

export default function PosCheckoutShow({ serviceSession, invoice, payments, paymentMethods, remainingBalanceRupiah }) {
    const { data, setData, post, processing, errors } = useForm({
        payment_method_code: paymentMethods[0]?.code ?? '',
        amount_rupiah: remainingBalanceRupiah,
        reference_number: '',
        notes: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post(route('pos.checkout.payments.store', serviceSession.id), {
            preserveScroll: true,
        });
    };

    const canPay = invoice.status === 'open' && remainingBalanceRupiah > 0;

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Kasir</p>
                        <h1 className="text-2xl font-bold text-white">POS Checkout</h1>
                    </div>
                    <Link
                        href={route('pos.sessions.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Sessions
                    </Link>
                </div>
            }
        >
            <Head title="POS Checkout" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
                        <section className="space-y-6 rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{invoice.invoiceCode}</p>
                                    <h2 className="mt-2 text-2xl font-bold text-white">{invoice.customerName ?? 'Walk-in customer'}</h2>
                                    <p className="mt-2 text-sm text-zinc-400">
                                        Session {serviceSession.sessionCode} · invoice {invoice.status}
                                    </p>
                                </div>
                                <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                    {invoice.status}
                                </div>
                            </div>

                            <div className="grid gap-3 md:grid-cols-4">
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Subtotal: <span className="font-semibold text-white">{formatRupiah(invoice.subtotalRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Discount: <span className="font-semibold text-white">{formatRupiah(invoice.discountRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Tax: <span className="font-semibold text-white">{formatRupiah(invoice.taxRupiah)}</span>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">
                                    Total: <span className="font-semibold text-white">{formatRupiah(invoice.grandTotalRupiah)}</span>
                                </div>
                            </div>

                            <div className="space-y-3">
                                <h3 className="text-lg font-semibold text-white">Invoice lines</h3>
                                {invoice.lines.map((line) => (
                                    <article key={line.id} className="rounded-2xl border border-white/10 bg-white/5 p-5">
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{line.lineType}</p>
                                                <h4 className="mt-2 text-lg font-semibold text-white">{line.description}</h4>
                                                <p className="mt-2 text-sm text-zinc-400">Qty {line.qty} · Unit {formatRupiah(line.unitPriceRupiah)}</p>
                                            </div>
                                            <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                                {formatRupiah(line.subtotalRupiah)}
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        </section>

                        <section className="space-y-6 rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                            <div>
                                <h2 className="text-xl font-semibold text-white">Payment status</h2>
                                <p className="mt-3 text-sm leading-6 text-zinc-400">
                                    Remaining balance: <span className="font-semibold text-white">{formatRupiah(remainingBalanceRupiah)}</span>
                                </p>
                            </div>

                            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
                                <div>
                                    <label className="text-sm font-medium text-zinc-200">Payment method</label>
                                    <select
                                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                        value={data.payment_method_code}
                                        onChange={(event) => setData('payment_method_code', event.target.value)}
                                    >
                                        {paymentMethods.map((paymentMethod) => (
                                            <option key={paymentMethod.code} value={paymentMethod.code}>
                                                {paymentMethod.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.payment_method_code && <p className="mt-2 text-sm text-rose-400">{errors.payment_method_code}</p>}
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-zinc-200">Amount rupiah</label>
                                    <input
                                        type="number"
                                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                        value={data.amount_rupiah}
                                        onChange={(event) => setData('amount_rupiah', Number(event.target.value))}
                                    />
                                    {errors.amount_rupiah && <p className="mt-2 text-sm text-rose-400">{errors.amount_rupiah}</p>}
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-zinc-200">Reference number</label>
                                    <input
                                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                        value={data.reference_number}
                                        onChange={(event) => setData('reference_number', event.target.value)}
                                    />
                                    {errors.reference_number && <p className="mt-2 text-sm text-rose-400">{errors.reference_number}</p>}
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-zinc-200">Notes</label>
                                    <textarea
                                        rows={4}
                                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                        value={data.notes}
                                        onChange={(event) => setData('notes', event.target.value)}
                                    />
                                    {errors.notes && <p className="mt-2 text-sm text-rose-400">{errors.notes}</p>}
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing || !canPay}
                                    className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
                                >
                                    Submit payment
                                </button>
                            </form>

                            <div className="space-y-3">
                                <h3 className="text-lg font-semibold text-white">Payments</h3>
                                {payments.length === 0 && (
                                    <div className="rounded-2xl border border-dashed border-white/15 bg-white/5 p-4 text-sm text-zinc-400">
                                        Belum ada payment tercatat.
                                    </div>
                                )}
                                {payments.map((payment) => (
                                    <article key={payment.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{payment.paymentMethodCode}</p>
                                                <h4 className="mt-2 text-lg font-semibold text-white">{formatRupiah(payment.amountRupiah)}</h4>
                                                <p className="mt-2 text-sm text-zinc-400">{payment.referenceNumber || 'Tanpa reference'} · {payment.verifiedByName}</p>
                                            </div>
                                            <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                                {payment.status}
                                            </div>
                                        </div>
                                    </article>
                                ))}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
