import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

const inputClass =
    'mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900/80 text-zinc-100 placeholder:text-zinc-500 focus:border-lime-300 focus:ring-lime-300';
const labelClass = 'text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout
            eyebrow="Account Recovery"
            title="Reset Password Staff"
            description="Masukkan email akun staff dan sistem akan mengirim tautan reset password."
        >
            <Head title="Forgot Password" />

            <div className="mb-6">
                <p className="street-heading-chip">Password Recovery</p>
                <p className="mt-3 text-sm leading-6 text-zinc-400">
                    Tautan reset akan dikirim ke email yang terdaftar pada akun staff.
                </p>
            </div>

            {status ? (
                <div className="mb-4 rounded-xl border border-lime-300/30 bg-lime-400/10 px-4 py-3 text-sm font-medium text-lime-200">
                    {status}
                </div>
            ) : null}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel className={labelClass} htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className={inputClass}
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="pt-2">
                    <PrimaryButton
                        className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 disabled:cursor-not-allowed"
                        disabled={processing}
                    >
                        Send Reset Link
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
