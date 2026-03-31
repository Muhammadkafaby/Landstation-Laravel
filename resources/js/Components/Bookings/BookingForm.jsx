import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import UnitLayoutPicker from '@/Components/Bookings/UnitLayoutPicker';
import { useForm } from '@inertiajs/react';
import { startTransition } from 'react';

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
    const selectedUnit = selectedUnits.find((unit) => unit.id === Number(data.service_unit_id)) ?? null;

    const submit = (event) => {
        event.preventDefault();

        post(route(routeName), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="max-w-full space-y-5 overflow-x-hidden rounded-3xl border border-white/10 bg-zinc-950/70 p-6">
            <div className="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                <div className="min-w-0 space-y-5">
                    <div className="grid gap-4 rounded-[2rem] border border-white/10 bg-white/[0.04] p-5 md:grid-cols-2">
                        <div>
                            <InputLabel htmlFor={`${routeName}-service`} value="Service" />
                            <select
                                id={`${routeName}-service`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={data.service_id}
                                onChange={(event) => {
                                    const serviceId = Number(event.target.value);
                                    const nextService = serviceOptions.find((service) => service.id === serviceId);

                                    startTransition(() => {
                                        setData((current) => ({
                                            ...current,
                                            service_id: serviceId,
                                            service_unit_id: nextService?.units[0]?.id ?? '',
                                        }));
                                    });
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

                        <div className="rounded-[1.5rem] border border-white/10 bg-zinc-950/60 px-4 py-4 text-sm text-zinc-300">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-emerald-300">
                                Unit Terpilih
                            </p>
                            <p className="mt-2 text-lg font-semibold text-white">
                                {selectedUnit ? selectedUnit.name : 'Belum memilih unit'}
                            </p>
                            <p className="mt-1 text-xs text-zinc-400">
                                {selectedUnit ? `${selectedUnit.code}${selectedUnit.zone ? ` · ${selectedUnit.zone}` : ''}` : 'Pilih langsung dari denah visual di bawah.'}
                            </p>
                        </div>

                        <div>
                            <InputLabel htmlFor={`${routeName}-start-at`} value="Mulai" />
                            <TextInput
                                id={`${routeName}-start-at`}
                                type="datetime-local"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
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
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={data.end_at}
                                onChange={(event) => setData('end_at', event.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.end_at} />
                        </div>
                    </div>

                    <UnitLayoutPicker
                        service={selectedService}
                        units={selectedUnits}
                        selectedUnitId={Number(data.service_unit_id) || null}
                        onSelect={(unitId) => setData('service_unit_id', unitId)}
                    />
                    <InputError className="mt-2" message={errors.service_unit_id} />
                </div>

                <div className="min-w-0 space-y-4 rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,_rgba(24,24,27,0.92),_rgba(9,9,11,0.98))] p-5">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-300/90">
                            Data Pemesan
                        </p>
                        <h2 className="mt-2 text-2xl font-black tracking-[0.03em] text-white">
                            Lengkapi detail booking
                        </h2>
                        <p className="mt-2 text-sm leading-6 text-zinc-400">
                            Setelah slot dikirim, sistem akan menahan unit selama 10 menit sambil staff melakukan konfirmasi manual.
                        </p>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <InputLabel htmlFor={`${routeName}-customer-name`} value="Nama customer" />
                            <TextInput
                                id={`${routeName}-customer-name`}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
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
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
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
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                value={data.customer_email}
                                onChange={(event) => setData('customer_email', event.target.value)}
                            />
                            <InputError className="mt-2" message={errors.customer_email} />
                        </div>

                        <div>
                            <InputLabel htmlFor={`${routeName}-notes`} value="Catatan" />
                            <textarea
                                id={`${routeName}-notes`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-emerald-500 focus:ring-emerald-500"
                                rows={5}
                                value={data.notes}
                                onChange={(event) => setData('notes', event.target.value)}
                            />
                            <InputError className="mt-2" message={errors.notes} />
                        </div>
                    </div>

                    <PrimaryButton disabled={processing || serviceOptions.length === 0 || ! data.service_unit_id}>
                        {submitLabel}
                    </PrimaryButton>
                </div>
            </div>
        </form>
    );
}
