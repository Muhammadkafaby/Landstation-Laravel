import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(amount);

export default function PosOrdersIndex({ productOptions, activeSessionOptions, bookingOptions, recentOrders }) {
    const firstCategory = productOptions[0];
    const firstProduct = firstCategory?.products[0];

    const { data, setData, post, processing, errors } = useForm({
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        booking_id: '',
        service_session_id: '',
        items: firstProduct
            ? [{ product_id: firstProduct.id, qty: 1, notes: '' }]
            : [],
    });

    const addItem = () => {
        const fallbackProduct = productOptions[0]?.products[0];

        if (!fallbackProduct) {
            return;
        }

        setData('items', [...data.items, { product_id: fallbackProduct.id, qty: 1, notes: '' }]);
    };

    const updateItem = (index, field, value) => {
        setData('items', data.items.map((item, itemIndex) => (
            itemIndex === index ? { ...item, [field]: value } : item
        )));
    };

    const submit = (event) => {
        event.preventDefault();

        post(route('pos.orders.store'), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Kasir</p>
                    <h1 className="text-2xl font-bold text-white">Cafe Order Control</h1>
                </div>
            }
        >
            <Head title="POS Orders" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Buat order cafe</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Order bisa dikaitkan ke booking atau session aktif, lalu semua item disimpan dengan snapshot harga agar aman untuk fase checkout berikutnya.
                        </p>
                    </div>

                    <form onSubmit={submit} className="space-y-5 rounded-3xl border border-white/10 bg-zinc-950/70 p-6">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <label className="text-sm font-medium text-zinc-200">Active session</label>
                                <select
                                    className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    value={data.service_session_id || ''}
                                    onChange={(event) => setData('service_session_id', event.target.value === '' ? '' : Number(event.target.value))}
                                >
                                    <option value="">Tanpa session</option>
                                    {activeSessionOptions.map((session) => (
                                        <option key={session.id} value={session.id}>
                                            {session.customerName} · {session.serviceName} · {session.unitName}
                                        </option>
                                    ))}
                                </select>
                                {errors.service_session_id && <p className="mt-2 text-sm text-rose-400">{errors.service_session_id}</p>}
                            </div>

                            <div>
                                <label className="text-sm font-medium text-zinc-200">Booking</label>
                                <select
                                    className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    value={data.booking_id || ''}
                                    onChange={(event) => setData('booking_id', event.target.value === '' ? '' : Number(event.target.value))}
                                >
                                    <option value="">Tanpa booking</option>
                                    {bookingOptions.map((booking) => (
                                        <option key={booking.id} value={booking.id}>
                                            {booking.bookingCode} · {booking.customerName} · {booking.serviceName}
                                        </option>
                                    ))}
                                </select>
                                {errors.booking_id && <p className="mt-2 text-sm text-rose-400">{errors.booking_id}</p>}
                            </div>

                            <div>
                                <label className="text-sm font-medium text-zinc-200">Nama customer</label>
                                <input
                                    className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    value={data.customer_name}
                                    onChange={(event) => setData('customer_name', event.target.value)}
                                />
                                {errors.customer_name && <p className="mt-2 text-sm text-rose-400">{errors.customer_name}</p>}
                            </div>

                            <div>
                                <label className="text-sm font-medium text-zinc-200">No. HP</label>
                                <input
                                    className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    value={data.customer_phone}
                                    onChange={(event) => setData('customer_phone', event.target.value)}
                                />
                                {errors.customer_phone && <p className="mt-2 text-sm text-rose-400">{errors.customer_phone}</p>}
                            </div>

                            <div>
                                <label className="text-sm font-medium text-zinc-200">Email</label>
                                <input
                                    type="email"
                                    className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    value={data.customer_email}
                                    onChange={(event) => setData('customer_email', event.target.value)}
                                />
                                {errors.customer_email && <p className="mt-2 text-sm text-rose-400">{errors.customer_email}</p>}
                            </div>
                        </div>

                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <h3 className="text-lg font-semibold text-white">Item order</h3>
                                <button
                                    type="button"
                                    onClick={addItem}
                                    className="inline-flex rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                                >
                                    Tambah item
                                </button>
                            </div>

                            {data.items.map((item, index) => (
                                <div key={index} className="grid gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 md:grid-cols-3">
                                    <div>
                                        <label className="text-sm font-medium text-zinc-200">Product</label>
                                        <select
                                            className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                            value={item.product_id}
                                            onChange={(event) => updateItem(index, 'product_id', Number(event.target.value))}
                                        >
                                            {productOptions.map((category) => (
                                                <optgroup key={category.id} label={category.name}>
                                                    {category.products.map((product) => (
                                                        <option key={product.id} value={product.id}>
                                                            {product.name} · {formatRupiah(product.priceRupiah)}
                                                        </option>
                                                    ))}
                                                </optgroup>
                                            ))}
                                        </select>
                                        {errors[`items.${index}.product_id`] && <p className="mt-2 text-sm text-rose-400">{errors[`items.${index}.product_id`]}</p>}
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-zinc-200">Qty</label>
                                        <input
                                            type="number"
                                            min="1"
                                            className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                            value={item.qty}
                                            onChange={(event) => updateItem(index, 'qty', Number(event.target.value))}
                                        />
                                        {errors[`items.${index}.qty`] && <p className="mt-2 text-sm text-rose-400">{errors[`items.${index}.qty`]}</p>}
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-zinc-200">Catatan</label>
                                        <input
                                            className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                            value={item.notes}
                                            onChange={(event) => updateItem(index, 'notes', event.target.value)}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>

                        <button
                            type="submit"
                            disabled={processing || data.items.length === 0}
                            className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
                        >
                            Simpan order cafe
                        </button>
                    </form>

                    <section className="space-y-4">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Recent orders</h2>
                            <p className="mt-2 text-sm text-zinc-400">Order terbaru yang sudah disubmit dan siap dipakai untuk fase checkout berikutnya.</p>
                        </div>

                        <div className="grid gap-4">
                            {recentOrders.map((order) => (
                                <article key={order.id} className="rounded-2xl border border-white/10 bg-white/5 p-5">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{order.orderCode}</p>
                                            <h3 className="mt-2 text-lg font-semibold text-white">{order.customerName ?? 'Walk-in customer'}</h3>
                                            <p className="mt-2 text-sm text-zinc-400">{order.status} · {order.itemsCount} items · {order.createdByName}</p>
                                        </div>
                                        <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                            {order.orderedAt}
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
