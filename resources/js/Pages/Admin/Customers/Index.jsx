import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(amount);

export default function CustomersIndex({ customers, filters }) {
    const { data, setData, processing } = useForm({
        q: filters.q ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        router.get(route('reports.customers.index'), { q: data.q }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    const clear = () => {
        setData('q', '');

        router.get(route('reports.customers.index'), { q: '' }, {
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
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Customer History</p>
                        <h1 className="text-2xl font-bold text-white">Customer Activity Overview</h1>
                    </div>
                    <Link
                        href={route('reports.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Reports
                    </Link>
                    <Link
                        href={route('reports.customers.export', { q: filters.q })}
                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                    >
                        Export CSV
                    </Link>
                </div>
            }
        >
            <Head title="Customer History" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Customer-centric activity summary</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Halaman ini menggabungkan histori booking, session, order, invoice, dan pembayaran tervalidasi per customer untuk memudahkan follow-up operasional.
                        </p>

                        <form onSubmit={submit} className="mt-5 flex flex-col gap-3 sm:flex-row">
                            <input
                                className="block w-full rounded-full border border-white/10 bg-zinc-950/70 px-4 py-2 text-sm text-white placeholder:text-zinc-500 focus:border-emerald-500 focus:outline-none focus:ring-0"
                                placeholder="Cari nama, phone, atau email"
                                value={data.q}
                                onChange={(event) => setData('q', event.target.value)}
                            />
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
                            >
                                Search
                            </button>
                            <button
                                type="button"
                                onClick={clear}
                                className="inline-flex rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                Clear
                            </button>
                        </form>
                    </div>

                    <div className="grid gap-6">
                        {customers.data.map((customer) => (
                            <article key={customer.id} className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{customer.phone || 'No phone'}</p>
                                        <h2 className="mt-2 text-2xl font-bold text-white">{customer.name}</h2>
                                        <p className="mt-2 text-sm text-zinc-400">{customer.email || 'Tanpa email'} · last activity {customer.lastActivityAt || '-'}</p>
                                    </div>
                                    <Link
                                        href={route('reports.customers.show', customer.id)}
                                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                                    >
                                        Buka detail
                                    </Link>
                                </div>

                                <div className="mt-6 grid gap-3 md:grid-cols-5 xl:grid-cols-6">
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Bookings: <span className="font-semibold text-white">{customer.bookingsCount}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Sessions: <span className="font-semibold text-white">{customer.sessionsCount}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Orders: <span className="font-semibold text-white">{customer.ordersCount}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300">Invoices: <span className="font-semibold text-white">{customer.invoicesCount}</span></div>
                                    <div className="rounded-2xl border border-white/10 bg-zinc-950/70 px-4 py-3 text-sm text-zinc-300 md:col-span-2">Verified payments: <span className="font-semibold text-white">{formatRupiah(customer.verifiedPaymentsRupiah)}</span></div>
                                </div>
                            </article>
                        ))}
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {customers.links.map((link, index) => (
                            link.url ? (
                                <Link
                                    key={`${link.label}-${index}`}
                                    href={link.url}
                                    className={`rounded-full px-4 py-2 text-sm font-semibold transition ${link.active ? 'bg-emerald-400 text-zinc-950' : 'border border-white/15 text-white hover:bg-white/10'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={`${link.label}-${index}`}
                                    className="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-500"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
