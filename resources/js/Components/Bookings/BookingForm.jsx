import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import UnitLayoutPicker from '@/Components/Bookings/UnitLayoutPicker';
import { useForm } from '@inertiajs/react';
import { startTransition } from 'react';

export default function BookingForm({ serviceOptions, preferredServiceId, routeName, submitLabel }) {
    const labelClass = 'text-sm font-semibold text-white';
    const preferredService = serviceOptions.find((service) => service.id === Number(preferredServiceId)) ?? null;
    const defaultService = preferredService ?? serviceOptions[0] ?? null;
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

    const selectedService =
        serviceOptions.find((service) => service.id === Number(data.service_id)) ??
        defaultService;
    const selectedUnits = selectedService?.units ?? [];
    const selectedUnit = selectedUnits.find((unit) => unit.id === Number(data.service_unit_id)) ?? null;

    const submit = (event) => {
        event.preventDefault();

        post(route(routeName), {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="street-shell rounded-[1.8rem] p-5 sm:p-6">
            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                <div className="min-w-0 space-y-5">
                    <div className="street-card grid gap-4 rounded-[1.5rem] p-5 md:grid-cols-2">
                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-service`}
                                value="Service"
                            />
                            <select
                                id={`${routeName}-service`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
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

                        <div className="street-pill rounded-[1.2rem] px-4 py-4 text-sm text-zinc-300">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-lime-200">Unit Terpilih</p>
                            <p className="mt-2 text-lg font-semibold text-white">{selectedUnit ? selectedUnit.name : 'Belum memilih unit'}</p>
                            <p className="mt-1 text-xs text-zinc-400">
                                {selectedUnit
                                    ? `${selectedUnit.code}${selectedUnit.zone ? ` | ${selectedUnit.zone}` : ''}`
                                    : 'Pilih langsung dari denah visual di bawah.'}
                            </p>
                        </div>

                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-start-at`}
                                value="Mulai"
                            />
                            <TextInput
                                id={`${routeName}-start-at`}
                                type="datetime-local"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.start_at}
                                onChange={(event) => setData('start_at', event.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.start_at} />
                        </div>

                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-end-at`}
                                value="Selesai"
                            />
                            <TextInput
                                id={`${routeName}-end-at`}
                                type="datetime-local"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
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

                <aside className="street-card min-w-0 space-y-5 rounded-[1.5rem] p-5">
                    <div>
                        <p className="street-heading-chip">Data Pemesan</p>
                        <h2 className="mt-3 text-2xl font-extrabold text-zinc-100">Lengkapi detail booking</h2>
                        <p className="mt-2 text-sm leading-6 text-zinc-400">
                            Setelah terkirim, slot masuk status hold selama 10 menit sambil menunggu konfirmasi staff.
                        </p>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-customer-name`}
                                value="Nama customer"
                            />
                            <TextInput
                                id={`${routeName}-customer-name`}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.customer_name}
                                onChange={(event) => setData('customer_name', event.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.customer_name} />
                        </div>

                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-customer-phone`}
                                value="No. HP / WhatsApp"
                            />
                            <TextInput
                                id={`${routeName}-customer-phone`}
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.customer_phone}
                                onChange={(event) => setData('customer_phone', event.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.customer_phone} />
                        </div>

                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-customer-email`}
                                value="Email"
                            />
                            <TextInput
                                id={`${routeName}-customer-email`}
                                type="email"
                                className="mt-1 block w-full border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                value={data.customer_email}
                                onChange={(event) => setData('customer_email', event.target.value)}
                            />
                            <InputError className="mt-2" message={errors.customer_email} />
                        </div>

                        <div>
                            <InputLabel
                                className={labelClass}
                                htmlFor={`${routeName}-notes`}
                                value="Catatan"
                            />
                            <textarea
                                id={`${routeName}-notes`}
                                className="mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900 text-white focus:border-lime-300 focus:ring-lime-300"
                                rows={4}
                                value={data.notes}
                                onChange={(event) => setData('notes', event.target.value)}
                            />
                            <InputError className="mt-2" message={errors.notes} />
                        </div>
                    </div>

                    <section className="street-pill rounded-[1.2rem] px-4 py-4 text-sm text-zinc-300">
                        <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-200">Ringkasan pilihan</p>
                        <p className="mt-2">Service: <span className="font-bold text-zinc-100">{selectedService?.name ?? '-'}</span></p>
                        <p className="mt-1">Unit: <span className="font-bold text-zinc-100">{selectedUnit?.name ?? '-'}</span></p>
                        <p className="mt-1 text-xs text-zinc-400">Hold berlaku 10 menit setelah submit.</p>
                    </section>

                    <PrimaryButton
                        id="booking-submit"
                        className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 disabled:cursor-not-allowed"
                        disabled={processing || serviceOptions.length === 0 || !data.service_unit_id}
                    >
                        {submitLabel}
                    </PrimaryButton>
                </aside>
            </div>
        </form>
    );
}
