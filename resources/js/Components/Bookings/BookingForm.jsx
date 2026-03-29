import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

export default function BookingForm({ serviceOptions, routeName, submitLabel }) {
    const defaultService = serviceOptions[0] ?? null;
    const defaultUnit = defaultService?.units[0] ?? null;

    const { data, setData, post, processing, errors } = useForm({
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        service_id: defaultService?.id ?? '',
        service_unit_id: defaultUnit?.id ?? '',
        start_at: '',
        end_at: '',
        notes: '',
    });

    const selectedService = serviceOptions.find((service) => service.id === Number(data.service_id)) ?? defaultService;
    const selectedUnits = selectedService?.units ?? [];

    const submit = (event) => {
        event.preventDefault();

        post(route(routeName), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-5 rounded-3xl border border-white/10 bg-zinc-950/70 p-6">
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <InputLabel htmlFor={`${routeName}-customer-name`} value="Nama customer" />
                    <TextInput
                        id={`${routeName}-customer-name`}
                        className="mt-1 block w-full"
                        value={data.customer_name}
                        onChange={(event) => setData('customer_name', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.customer_name} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-customer-phone`} value="No. HP / WhatsApp" />
                    <TextInput
                        id={`${routeName}-customer-phone`}
                        className="mt-1 block w-full"
                        value={data.customer_phone}
                        onChange={(event) => setData('customer_phone', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.customer_phone} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-customer-email`} value="Email" />
                    <TextInput
                        id={`${routeName}-customer-email`}
                        type="email"
                        className="mt-1 block w-full"
                        value={data.customer_email}
                        onChange={(event) => setData('customer_email', event.target.value)}
                    />
                    <InputError className="mt-2" message={errors.customer_email} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-service`} value="Service" />
                    <select
                        id={`${routeName}-service`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_id}
                        onChange={(event) => {
                            const serviceId = Number(event.target.value);
                            const nextService = serviceOptions.find((service) => service.id === serviceId);

                            setData((current) => ({
                                ...current,
                                service_id: serviceId,
                                service_unit_id: nextService?.units[0]?.id ?? '',
                            }));
                        }}
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
                    <InputLabel htmlFor={`${routeName}-service-unit`} value="Unit" />
                    <select
                        id={`${routeName}-service-unit`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        value={data.service_unit_id || ''}
                        onChange={(event) => setData('service_unit_id', event.target.value === '' ? '' : Number(event.target.value))}
                    >
                        <option value="">Pilih unit</option>
                        {selectedUnits.map((unit) => (
                            <option key={unit.id} value={unit.id}>
                                {unit.name} ({unit.code})
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.service_unit_id} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-start-at`} value="Mulai" />
                    <TextInput
                        id={`${routeName}-start-at`}
                        type="datetime-local"
                        className="mt-1 block w-full"
                        value={data.start_at}
                        onChange={(event) => setData('start_at', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.start_at} />
                </div>

                <div>
                    <InputLabel htmlFor={`${routeName}-end-at`} value="Selesai" />
                    <TextInput
                        id={`${routeName}-end-at`}
                        type="datetime-local"
                        className="mt-1 block w-full"
                        value={data.end_at}
                        onChange={(event) => setData('end_at', event.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.end_at} />
                </div>

                <div className="xl:col-span-3">
                    <InputLabel htmlFor={`${routeName}-notes`} value="Catatan" />
                    <textarea
                        id={`${routeName}-notes`}
                        className="mt-1 block w-full rounded-md border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                        rows={4}
                        value={data.notes}
                        onChange={(event) => setData('notes', event.target.value)}
                    />
                    <InputError className="mt-2" message={errors.notes} />
                </div>
            </div>

            <PrimaryButton disabled={processing || serviceOptions.length === 0}>{submitLabel}</PrimaryButton>
        </form>
    );
}
