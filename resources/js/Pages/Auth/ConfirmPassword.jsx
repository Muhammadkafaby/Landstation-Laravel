import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

const inputClass =
    'mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900/80 text-zinc-100 placeholder:text-zinc-500 focus:border-lime-300 focus:ring-lime-300';
const labelClass = 'text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout
            eyebrow="Protected Session"
            title="Konfirmasi Password"
            description="Sebelum lanjut ke area sensitif, verifikasi password akun staff Anda."
        >
            <Head title="Confirm Password" />

            <div className="mb-6">
                <p className="street-heading-chip">Security Gate</p>
                <p className="mt-3 text-sm leading-6 text-zinc-400">
                    Langkah ini melindungi aksi sensitif di dashboard dari akses yang tidak sah.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel className={labelClass} htmlFor="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className={inputClass}
                        isFocused={true}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="pt-2">
                    <PrimaryButton
                        className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 disabled:cursor-not-allowed"
                        disabled={processing}
                    >
                        Confirm
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
