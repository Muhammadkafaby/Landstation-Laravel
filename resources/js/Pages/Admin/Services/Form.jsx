import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

export default function ServiceForm({
    categories,
    options,
    service,
    submitLabel,
    routeName,
    method = 'post',
}) {
    const { data, setData, post, patch, processing, errors } = useForm({
        service_category_id: service?.serviceCategoryId ?? categories[0]?.id ?? '',
        code: service?.code ?? '',
        name: service?.name ?? '',
        slug: service?.slug ?? '',
        service_type: service?.serviceType ?? options.serviceTypes[0]?.value ?? '',
        billing_type: service?.billingType ?? options.billingTypes[0]?.value ?? '',
        layout_mode: service?.layoutMode ?? 'manual_grid',
        layout_canvas_width: service?.layoutCanvasWidth ?? 960,
        layout_canvas_height: service?.layoutCanvasHeight ?? 640,
        sort_order: service?.sortOrder ?? 0,
        is_active: service?.isActive ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        const action = method === 'patch' ? patch : post;

        action(route(routeName, service?.id), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4 rounded-2xl border border-white/10 bg-zinc-950/70 p-5">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <InputLabel htmlFor={`${routeName}-category`} value="Kategori" />
                    <select
                        id={`${routeName}-category`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_category_id}
                        onChange={(event) => setData('service_category_id', Number(event.target.value))}
                    >
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_category_id} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-name`} value="Nama service" />
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
                    <InputLabel htmlFor={`${routeName}-slug`} value="Slug" />
                    <TextInput
                        id={`${routeName}-slug`}
                        className="mt-1 block w-full"
                        value={data.slug}
                        onChange={(event) => setData('slug', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.slug} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-service-type`} value="Service type" />
                    <select
                        id={`${routeName}-service-type`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_type}
                        onChange={(event) => setData('service_type', event.target.value)}
                    >
                        {options.serviceTypes.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_type} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-billing-type`} value="Billing type" />
                    <select
                        id={`${routeName}-billing-type`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.billing_type}
                        onChange={(event) => setData('billing_type', event.target.value)}
                    >
                        {options.billingTypes.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.billing_type} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-sort-order`} value="Sort order" />
                    <TextInput
                        id={`${routeName}-sort-order`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.sort_order}
                        onChange={(event) => setData('sort_order', Number(event.target.value))}
                        required
                    />
                    <InputError className="mt-2" message={errors.sort_order} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-layout-mode`} value="Layout mode" />
                    <TextInput
                        id={`${routeName}-layout-mode`}
                        className="mt-1 block w-full"
                        value={data.layout_mode || ''}
                        onChange={(event) => setData('layout_mode', event.target.value)}
                    />
                    <InputError className="mt-2" message={errors.layout_mode} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-layout-width`} value="Canvas width" />
                    <TextInput
                        id={`${routeName}-layout-width`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.layout_canvas_width || ''}
                        onChange={(event) => setData('layout_canvas_width', event.target.value === '' ? null : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.layout_canvas_width} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-layout-height`} value="Canvas height" />
                    <TextInput
                        id={`${routeName}-layout-height`}
                        type="number"
                        className="mt-1 block w-full"
                        value={data.layout_canvas_height || ''}
                        onChange={(event) => setData('layout_canvas_height', event.target.value === '' ? null : Number(event.target.value))}
                    />
                    <InputError className="mt-2" message={errors.layout_canvas_height} />
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
