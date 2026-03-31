import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const inputClass =
    'mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900/80 text-zinc-100 placeholder:text-zinc-500 focus:border-lime-300 focus:ring-lime-300';
const labelClass = 'text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout
            eyebrow="Staff Onboarding"
            title="Buat Akun Staff"
            description="Pendaftaran staff tetap membutuhkan role dan izin dari sistem internal sebelum bisa masuk area operasional."
        >
            <Head title="Register" />

            <div className="mb-6">
                <p className="street-heading-chip">Create Account</p>
                <h2 className="mt-3 text-2xl font-extrabold text-zinc-100">
                    Daftarkan akun internal
                </h2>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel className={labelClass} htmlFor="name" value="Name" />
                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className={inputClass}
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel className={labelClass} htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className={inputClass}
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel className={labelClass} htmlFor="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className={inputClass}
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel className={labelClass} htmlFor="password_confirmation" value="Confirm Password" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className={inputClass}
                        autoComplete="new-password"
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={route('login')}
                        className="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-400 underline decoration-zinc-600 underline-offset-4 transition hover:text-zinc-200"
                    >
                        Already Registered?
                    </Link>

                    <PrimaryButton
                        className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 sm:w-auto disabled:cursor-not-allowed"
                        disabled={processing}
                    >
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
