import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(amount);

export default function CustomersShow({ customer, bookings, sessions, orders, invoices }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Customer Detail</p>
                        <h1 className="text-2xl font-bold text-white">{customer.name}</h1>
                    </div>
                    <Link
                        href={route('reports.customers.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Customer History
                    </Link>
                </div>
            }
        >
            <Head title={`Customer · ${customer.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-6 xl:grid-cols-[0.95fr,1.05fr]">
                        <section className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6">
                            <h2 className="text-xl font-semibold text-white">Profile summary</h2>
                            <div className="mt-4 grid gap-3">
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Phone: <span className="font-semibold text-white">{customer.phone || '-'}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Email: <span className="font-semibold text-white">{customer.email || '-'}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Bookings: <span className="font-semibold text-white">{customer.bookingsCount}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Sessions: <span className="font-semibold text-white">{customer.sessionsCount}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Orders: <span className="font-semibold text-white">{customer.ordersCount}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Invoices: <span className="font-semibold text-white">{customer.invoicesCount}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Verified payments: <span className="font-semibold text-white">{formatRupiah(customer.verifiedPaymentsRupiah)}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Last activity: <span className="font-semibold text-white">{customer.lastActivityAt || '-'}</span></div>
                                <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Notes: <span className="font-semibold text-white">{customer.notes || '-'}</span></div>
                            </div>
                        </section>

                        <section className="space-y-6">
                            <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <h2 className="text-xl font-semibold text-white">Bookings</h2>
                                <div className="mt-4 grid gap-4">
                                    {bookings.map((booking) => (
                                        <article key={booking.id} className="rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-sm text-zinc-300">
                                            <div className="font-semibold text-white">{booking.bookingCode} · {booking.status}</div>
                                            <div className="mt-2">{booking.serviceName} · {booking.unitName || 'Tanpa unit'} · {booking.source}</div>
                                            <div className="mt-2 text-zinc-500">{booking.startAt} → {booking.endAt}</div>
                                        </article>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <h2 className="text-xl font-semibold text-white">Service sessions</h2>
                                <div className="mt-4 grid gap-4">
                                    {sessions.map((session) => (
                                        <article key={session.id} className="rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-sm text-zinc-300">
                                            <div className="font-semibold text-white">{session.sessionCode} · {session.status}</div>
                                            <div className="mt-2">{session.serviceName} · {session.unitName || 'Tanpa unit'} · booking {session.bookingCode || '-'}</div>
                                            <div className="mt-2">Billed minutes: <span className="font-semibold text-white">{session.billedMinutes}</span></div>
                                            <div className="mt-2 text-zinc-500">{session.startedAt} → {session.endedAt || '-'}</div>
                                        </article>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <h2 className="text-xl font-semibold text-white">Orders</h2>
                                <div className="mt-4 grid gap-4">
                                    {orders.map((order) => (
                                        <article key={order.id} className="rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-sm text-zinc-300">
                                            <div className="font-semibold text-white">{order.orderCode} · {order.status}</div>
                                            <div className="mt-2">Items: <span className="font-semibold text-white">{order.itemsCount}</span></div>
                                            <div className="mt-2 text-zinc-500">{order.orderedAt}</div>
                                        </article>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <h2 className="text-xl font-semibold text-white">Invoices & payments</h2>
                                <div className="mt-4 grid gap-4">
                                    {invoices.map((invoice) => (
                                        <article key={invoice.id} className="rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-sm text-zinc-300">
                                            <div className="font-semibold text-white">{invoice.invoiceCode} · {invoice.status}</div>
                                            <div className="mt-2">Total: <span className="font-semibold text-white">{formatRupiah(invoice.grandTotalRupiah)}</span></div>
                                            <div className="mt-2 text-zinc-500">Issued {invoice.issuedAt} · Closed {invoice.closedAt || '-'}</div>
                                            <div className="mt-3 grid gap-3">
                                                {invoice.payments.map((payment) => (
                                                    <div key={payment.id} className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                                        <div className="font-semibold text-white">{payment.paymentMethodCode} · {payment.status}</div>
                                                        <div className="mt-1">{formatRupiah(payment.amountRupiah)} · {payment.referenceNumber || '-'}</div>
                                                        <div className="mt-1 text-zinc-500">{payment.paidAt || '-'} · {payment.verifiedByName || '-'}</div>
                                                    </div>
                                                ))}
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
