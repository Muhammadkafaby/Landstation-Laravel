import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ServiceForm from '@/Pages/Admin/Services/Form';
import { Head, Link, useForm } from '@inertiajs/react';

function CategoryForm({ category, routeName, submitLabel }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        code: category?.code ?? '',
        name: category?.name ?? '',
        description: category?.description ?? '',
        is_active: category?.isActive ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        const action = category ? patch : post;

        action(route(routeName, category?.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <InputLabel htmlFor={`${routeName}-code`} value="Code" />
                    <TextInput
                        id={`${routeName}-code`}
                        className="mt-1 block w-full"
                        value={data.code}
                        onChange={(event) => setData('code', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.code} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-name`} value="Nama kategori" />
                    <TextInput
                        id={`${routeName}-name`}
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div className="md:col-span-2">
                    <InputLabel htmlFor={`${routeName}-description`} value="Deskripsi" />
                    <textarea
                        id={`${routeName}-description`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        rows={3}
                        value={data.description}
                        onChange={(event) => setData('description', event.target.value)}
                    />
                    <InputError className="mt-2" message={errors.description} />
                </div>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300 md:col-span-2">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.is_active}
                        onChange={(event) => setData('is_active', event.target.checked)}
                    />
                    Aktif
                </label>
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}

function UnitForm({ unit, unitServices, unitStatuses, routeName, submitLabel }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        service_id: unit?.serviceId ?? unitServices[0]?.id ?? '',
        code: unit?.code ?? '',
        name: unit?.name ?? '',
        zone: unit?.zone ?? '',
        status: unit?.status ?? unitStatuses[0]?.value ?? '',
        capacity: unit?.capacity ?? '',
        is_bookable: unit?.isBookable ?? true,
        is_active: unit?.isActive ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        const action = unit ? patch : post;

        action(route(routeName, unit?.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <InputLabel htmlFor={`${routeName}-service`} value="Service" />
                    <select
                        id={`${routeName}-service`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_id}
                        onChange={(event) => setData('service_id', Number(event.target.value))}
                    >
                        {unitServices.map((service) => (
                            <option key={service.id} value={service.id}>
                                {service.name} ({service.code})
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_id} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-code`} value="Code" />
                    <TextInput
                        id={`${routeName}-code`}
                        className="mt-1 block w-full"
                        value={data.code}
                        onChange={(event) => setData('code', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.code} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-name`} value="Nama unit" />
                    <TextInput
                        id={`${routeName}-name`}
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-zone`} value="Zone" />
                    <TextInput
                        id={`${routeName}-zone`}
                        className="mt-1 block w-full"
                        value={data.zone}
                        onChange={(event) => setData('zone', event.target.value)}
                    />
                    <InputError className="mt-2" message={errors.zone} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-status`} value="Status" />
                    <select
                        id={`${routeName}-status`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.status}
                        onChange={(event) => setData('status', event.target.value)}
                    >
                        {unitStatuses.map((status) => (
                            <option key={status.value} value={status.value}>
                                {status.label}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.status} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-capacity`} value="Capacity" />
                    <TextInput
                        id={`${routeName}-capacity`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.capacity}
                        onChange={(event) => setData('capacity', event.target.value === '' ? '' : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.capacity} />
                </div>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.is_bookable}
                        onChange={(event) => setData('is_bookable', event.target.checked)}
                    />
                    Bookable
                </label>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.is_active}
                        onChange={(event) => setData('is_active', event.target.checked)}
                    />
                    Aktif
                </label>
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}

function PricingRuleForm({ pricingRule, pricingServices, unitOptions, pricingModels, routeName, submitLabel }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        service_id: pricingRule?.serviceId ?? pricingServices[0]?.id ?? '',
        service_unit_id: pricingRule?.serviceUnitId ?? '',
        pricing_model: pricingRule?.pricingModel ?? pricingModels[0]?.value ?? '',
        billing_interval_minutes: pricingRule?.billingIntervalMinutes ?? '',
        base_price_rupiah: pricingRule?.basePriceRupiah ?? 0,
        price_per_interval_rupiah: pricingRule?.pricePerIntervalRupiah ?? '',
        minimum_charge_rupiah: pricingRule?.minimumChargeRupiah ?? '',
        priority: pricingRule?.priority ?? 0,
        is_active: pricingRule?.isActive ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        const action = pricingRule ? patch : post;

        action(route(routeName, pricingRule?.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <InputLabel htmlFor={`${routeName}-service`} value="Service" />
                    <select
                        id={`${routeName}-service`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_id}
                        onChange={(event) => setData('service_id', Number(event.target.value))}
                    >
                        {pricingServices.map((service) => (
                            <option key={service.id} value={service.id}>
                                {service.name} ({service.code})
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_id} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-service-unit`} value="Unit spesifik" />
                    <select
                        id={`${routeName}-service-unit`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_unit_id || ''}
                        onChange={(event) => setData('service_unit_id', event.target.value === '' ? null : Number(event.target.value))}
                    >
                        <option value="">Default service-wide</option>
                        {unitOptions.map((unit) => (
                            <option key={unit.id} value={unit.id}>
                                {unit.name} ({unit.code})
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_unit_id} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-pricing-model`} value="Pricing model" />
                    <select
                        id={`${routeName}-pricing-model`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.pricing_model}
                        onChange={(event) => setData('pricing_model', event.target.value)}
                    >
                        {pricingModels.map((model) => (
                            <option key={model.value} value={model.value}>
                                {model.label}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.pricing_model} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-interval`} value="Billing interval (minutes)" />
                    <TextInput
                        id={`${routeName}-interval`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.billing_interval_minutes}
                        onChange={(event) => setData('billing_interval_minutes', event.target.value === '' ? '' : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.billing_interval_minutes} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-base-price`} value="Base price rupiah" />
                    <TextInput
                        id={`${routeName}-base-price`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.base_price_rupiah}
                        onChange={(event) => setData('base_price_rupiah', Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.base_price_rupiah} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-interval-price`} value="Price per interval rupiah" />
                    <TextInput
                        id={`${routeName}-interval-price`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.price_per_interval_rupiah}
                        onChange={(event) => setData('price_per_interval_rupiah', event.target.value === '' ? '' : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.price_per_interval_rupiah} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-minimum-charge`} value="Minimum charge rupiah" />
                    <TextInput
                        id={`${routeName}-minimum-charge`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.minimum_charge_rupiah}
                        onChange={(event) => setData('minimum_charge_rupiah', event.target.value === '' ? '' : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.minimum_charge_rupiah} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-priority`} value="Priority" />
                    <TextInput
                        id={`${routeName}-priority`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.priority}
                        onChange={(event) => setData('priority', Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.priority} />
                </div>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.is_active}
                        onChange={(event) => setData('is_active', event.target.checked)}
                    />
                    Aktif
                </label>
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}

function BookingPolicyForm({ bookingPolicy, serviceOptions, routeName, submitLabel }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        service_id: bookingPolicy?.serviceId ?? serviceOptions[0]?.id ?? '',
        slot_interval_minutes: bookingPolicy?.slotIntervalMinutes ?? 30,
        min_duration_minutes: bookingPolicy?.minDurationMinutes ?? 60,
        max_duration_minutes: bookingPolicy?.maxDurationMinutes ?? '',
        lead_time_minutes: bookingPolicy?.leadTimeMinutes ?? 0,
        max_advance_days: bookingPolicy?.maxAdvanceDays ?? 30,
        requires_unit_assignment: bookingPolicy?.requiresUnitAssignment ?? true,
        walk_in_allowed: bookingPolicy?.walkInAllowed ?? true,
        online_booking_allowed: bookingPolicy?.onlineBookingAllowed ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        const action = bookingPolicy ? patch : post;

        action(route(routeName, bookingPolicy?.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <InputLabel htmlFor={`${routeName}-service`} value="Service" />
                    <select
                        id={`${routeName}-service`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_id}
                        onChange={(event) => setData('service_id', Number(event.target.value))}
                    >
                        {serviceOptions.map((service) => (
                            <option key={service.id} value={service.id}>
                                {service.name} ({service.code})
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_id} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-slot`} value="Slot interval minutes" />
                    <TextInput
                        id={`${routeName}-slot`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.slot_interval_minutes}
                        onChange={(event) => setData('slot_interval_minutes', Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.slot_interval_minutes} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-min-duration`} value="Min duration minutes" />
                    <TextInput
                        id={`${routeName}-min-duration`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.min_duration_minutes}
                        onChange={(event) => setData('min_duration_minutes', Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.min_duration_minutes} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-max-duration`} value="Max duration minutes" />
                    <TextInput
                        id={`${routeName}-max-duration`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.max_duration_minutes}
                        onChange={(event) => setData('max_duration_minutes', event.target.value === '' ? '' : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.max_duration_minutes} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-lead-time`} value="Lead time minutes" />
                    <TextInput
                        id={`${routeName}-lead-time`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.lead_time_minutes}
                        onChange={(event) => setData('lead_time_minutes', Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.lead_time_minutes} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-advance-days`} value="Max advance days" />
                    <TextInput
                        id={`${routeName}-advance-days`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.max_advance_days}
                        onChange={(event) => setData('max_advance_days', Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.max_advance_days} />
                </div>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.requires_unit_assignment}
                        onChange={(event) => setData('requires_unit_assignment', event.target.checked)}
                    />
                    Requires unit assignment
                </label>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.walk_in_allowed}
                        onChange={(event) => setData('walk_in_allowed', event.target.checked)}
                    />
                    Walk-in allowed
                </label>

                <label className="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-4 py-3 text-sm text-zinc-300">
                    <input
                        type="checkbox"
                        className="rounded border-zinc-700 bg-zinc-900 text-emerald-500 focus:ring-emerald-500"
                        checked={data.online_booking_allowed}
                        onChange={(event) => setData('online_booking_allowed', event.target.checked)}
                    />
                    Online booking allowed
                </label>
            </div>

            <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
        </form>
    );
}

export default function ServiceCatalogIndex({ categories, services, units, pricingRules, bookingPolicies, options }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                            Management
                        </p>
                        <h1 className="text-2xl font-bold text-white">Service Catalog CRUD Foundation</h1>
                    </div>
                    <Link
                        href={route('management.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Management
                    </Link>
                </div>
            }
        >
            <Head title="Service Catalog" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <h2 className="text-xl font-semibold text-white">Master data service mulai bisa dikelola dari admin</h2>
                        <p className="mt-3 max-w-3xl text-sm leading-6 text-zinc-400">
                            Slice ini membuka create dan update untuk kategori layanan, service inti, dan unit operasional dasar. Pricing dan booking policy akan menyusul di komponen berikutnya.
                        </p>
                    </div>

                    <section className="space-y-6">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Kategori layanan</h2>
                            <p className="mt-2 text-sm text-zinc-400">Tambah atau ubah kategori dasar seperti cafe, billiard, PlayStation, dan rental RC.</p>
                        </div>

                        <CategoryForm routeName="management.service-categories.store" submitLabel="Tambah kategori" />

                        <div className="grid gap-6 xl:grid-cols-2">
                            {categories.map((category) => (
                                <CategoryForm
                                    key={category.id}
                                    category={category}
                                    routeName="management.service-categories.update"
                                    submitLabel={`Update ${category.name}`}
                                />
                            ))}
                        </div>
                    </section>

                    <section className="space-y-6">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Services</h2>
                            <p className="mt-2 text-sm text-zinc-400">Kelola service yang akan dipakai oleh public site, dashboard, booking, dan POS.</p>
                        </div>

                        <ServiceForm
                            categories={categories}
                            options={options}
                            routeName="management.services.store"
                            submitLabel="Tambah service"
                        />

                        <div className="grid gap-6">
                            {services.map((service) => (
                                <div key={service.id} className="space-y-3 rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                                {service.categoryCode}
                                            </p>
                                            <h3 className="mt-1 text-xl font-semibold text-white">{service.name}</h3>
                                            <p className="mt-2 text-sm text-zinc-400">
                                                {service.code} · {service.slug} · {service.serviceType} · {service.billingType}
                                            </p>
                                        </div>
                                        <span className="inline-flex rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                            Sort {service.sortOrder}
                                        </span>
                                    </div>

                                    <ServiceForm
                                        categories={categories}
                                        options={options}
                                        service={service}
                                        routeName="management.services.update"
                                        method="patch"
                                        submitLabel={`Update ${service.name}`}
                                    />
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="space-y-6">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Service units</h2>
                            <p className="mt-2 text-sm text-zinc-400">Kelola unit fisik untuk layanan bertipe timed unit seperti PlayStation, billiard, dan rental RC.</p>
                        </div>

                        <UnitForm
                            unitServices={options.unitServices}
                            unitStatuses={options.unitStatuses}
                            routeName="management.service-units.store"
                            submitLabel="Tambah unit"
                        />

                        <div className="grid gap-6">
                            {units.map((unit) => (
                                <div key={unit.id} className="space-y-3 rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                                {unit.serviceCode}
                                            </p>
                                            <h3 className="mt-1 text-xl font-semibold text-white">{unit.name}</h3>
                                            <p className="mt-2 text-sm text-zinc-400">
                                                {unit.code} · {unit.zone || 'Tanpa zone'} · {unit.status}
                                            </p>
                                        </div>
                                        <span className="inline-flex rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                            Cap {unit.capacity || '-'}
                                        </span>
                                    </div>

                                    <UnitForm
                                        unit={unit}
                                        unitServices={options.unitServices}
                                        unitStatuses={options.unitStatuses}
                                        routeName="management.service-units.update"
                                        submitLabel={`Update ${unit.name}`}
                                    />
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="space-y-6">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Pricing rules</h2>
                            <p className="mt-2 text-sm text-zinc-400">Kelola aturan harga per service atau override per unit dengan field rupiah bertipe integer.</p>
                        </div>

                        <PricingRuleForm
                            pricingServices={options.pricingServices}
                            unitOptions={units}
                            pricingModels={options.pricingModels}
                            routeName="management.service-pricing-rules.store"
                            submitLabel="Tambah pricing rule"
                        />

                        <div className="grid gap-6">
                            {pricingRules.map((pricingRule) => (
                                <div key={pricingRule.id} className="space-y-3 rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                                {pricingRule.serviceCode}
                                            </p>
                                            <h3 className="mt-1 text-xl font-semibold text-white">
                                                {pricingRule.unitName ? `${pricingRule.serviceName} · ${pricingRule.unitName}` : pricingRule.serviceName}
                                            </h3>
                                            <p className="mt-2 text-sm text-zinc-400">
                                                {pricingRule.pricingModel} · interval {pricingRule.billingIntervalMinutes || '-'} menit · priority {pricingRule.priority}
                                            </p>
                                        </div>
                                        <span className="inline-flex rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                            {pricingRule.isActive ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>

                                    <PricingRuleForm
                                        pricingRule={pricingRule}
                                        pricingServices={options.pricingServices}
                                        unitOptions={units}
                                        pricingModels={options.pricingModels}
                                        routeName="management.service-pricing-rules.update"
                                        submitLabel={`Update pricing ${pricingRule.id}`}
                                    />
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="space-y-6">
                        <div>
                            <h2 className="text-xl font-semibold text-white">Booking policies</h2>
                            <p className="mt-2 text-sm text-zinc-400">Kelola aturan slot dan durasi booking untuk service timed unit. Satu service hanya memiliki satu policy aktif.</p>
                        </div>

                        <BookingPolicyForm
                            serviceOptions={options.bookingPolicyServices}
                            routeName="management.service-booking-policies.store"
                            submitLabel="Tambah booking policy"
                        />

                        <div className="grid gap-6">
                            {bookingPolicies.map((bookingPolicy) => (
                                <div key={bookingPolicy.id} className="space-y-3 rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">
                                                {bookingPolicy.serviceCode}
                                            </p>
                                            <h3 className="mt-1 text-xl font-semibold text-white">{bookingPolicy.serviceName}</h3>
                                            <p className="mt-2 text-sm text-zinc-400">
                                                slot {bookingPolicy.slotIntervalMinutes} menit · min {bookingPolicy.minDurationMinutes} menit · lead {bookingPolicy.leadTimeMinutes} menit
                                            </p>
                                        </div>
                                        <span className="inline-flex rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                            advance {bookingPolicy.maxAdvanceDays} hari
                                        </span>
                                    </div>

                                    <BookingPolicyForm
                                        bookingPolicy={bookingPolicy}
                                        serviceOptions={options.bookingPolicyServices}
                                        routeName="management.service-booking-policies.update"
                                        submitLabel={`Update policy ${bookingPolicy.serviceName}`}
                                    />
                                </div>
                            ))}
                        </div>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
