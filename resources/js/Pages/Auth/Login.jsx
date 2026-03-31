import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const inputClass =
    'mt-1 block w-full rounded-xl border-zinc-700 bg-zinc-900/80 text-zinc-100 placeholder:text-zinc-500 focus:border-lime-300 focus:ring-lime-300';
const labelClass = 'text-xs font-semibold uppercase tracking-[0.16em] text-zinc-300';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout
            eyebrow="Staff Login"
            title="Masuk Ke Dashboard Internal"
            description="Gunakan akun staff yang aktif untuk akses area admin, POS, dan antrian booking hold."
        >
            <Head title="Login Staff" />

            <div className="mb-6">
                <p className="street-heading-chip">Secure Access</p>
                <h2 className="mt-3 text-2xl font-extrabold text-zinc-100">
                    Login staff Land Station
                </h2>
                <p className="mt-2 text-sm leading-6 text-zinc-400">
                    Sistem akan menolak akun tanpa role dan permission yang valid.
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
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
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
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between gap-3 pt-1">
                    <label className="flex items-center gap-2 text-sm text-zinc-300">
                        <Checkbox
                            className="border-zinc-600 bg-zinc-900 text-lime-300 focus:ring-lime-300"
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        Remember me
                    </label>

                    {canResetPassword ? (
                        <Link
                            href={route('password.request')}
                            className="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-400 underline decoration-zinc-600 underline-offset-4 transition hover:text-zinc-200"
                        >
                            Forgot Password
                        </Link>
                    ) : null}
                </div>

                <div className="pt-2">
                    <PrimaryButton
                        className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 disabled:cursor-not-allowed"
                        disabled={processing}
                    >
                        Log In
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
