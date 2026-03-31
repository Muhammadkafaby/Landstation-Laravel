import PrimaryButton from '@/Components/PrimaryButton';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout
            eyebrow="Email Verification"
            title="Verifikasi Email Staff"
            description="Aktifkan verifikasi agar akses staff tetap aman sebelum masuk ke area operasional."
        >
            <Head title="Email Verification" />

            <div className="mb-6">
                <p className="street-heading-chip">One More Step</p>
                <p className="mt-3 text-sm leading-6 text-zinc-400">
                    Klik tautan verifikasi dari email. Jika belum masuk, kirim ulang dari tombol di bawah.
                </p>
            </div>

            {status === 'verification-link-sent' ? (
                <div className="mb-4 rounded-xl border border-lime-300/30 bg-lime-400/10 px-4 py-3 text-sm font-medium text-lime-200">
                    Link verifikasi baru sudah dikirim ke email Anda.
                </div>
            ) : null}

            <form onSubmit={submit} className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <PrimaryButton
                    className="street-cta-primary street-motion w-full justify-center rounded-full border px-5 py-3 text-xs tracking-[0.16em] hover:-translate-y-0.5 sm:w-auto disabled:cursor-not-allowed"
                    disabled={processing}
                >
                    Resend Verification Email
                </PrimaryButton>

                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    className="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-400 underline decoration-zinc-600 underline-offset-4 transition hover:text-zinc-200"
                >
                    Log Out
                </Link>
            </form>
        </GuestLayout>
    );
}
