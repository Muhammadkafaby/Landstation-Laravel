import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="relative flex min-h-screen items-center justify-center overflow-hidden bg-zinc-950 px-6 py-10">
            <div className="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.22),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(59,130,246,0.18),_transparent_30%)]" />

            <div className="grid w-full max-w-6xl gap-8 lg:grid-cols-[1fr,420px] lg:items-center">
                <div className="hidden lg:block">
                    <p className="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-300">
                        Staff Access
                    </p>
                    <h1 className="mt-4 max-w-xl text-4xl font-black tracking-tight text-white">
                        Pusat operasional Land Station untuk booking, POS, dan monitoring unit.
                    </h1>
                    <p className="mt-5 max-w-lg text-base leading-7 text-zinc-400">
                        Gunakan akun staff untuk masuk ke dashboard internal dan mengelola operasional harian bisnis.
                    </p>
                </div>

                <div className="w-full">
                    <div className="mb-6 flex justify-center lg:justify-start">
                        <Link href="/">
                            <ApplicationLogo className="text-white" />
                        </Link>
                    </div>

                    <div className="w-full overflow-hidden rounded-3xl border border-white/10 bg-white/95 px-6 py-6 shadow-2xl shadow-black/30 sm:px-8">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
