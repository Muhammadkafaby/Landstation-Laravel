import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

const signals = [
    'Admin dashboard dan report',
    'POS dan session control',
    'Booking hold confirmation',
];

export default function GuestLayout({
    children,
    eyebrow = 'Staff Access',
    title = 'Kontrol operasional Land Station',
    description = 'Masuk dengan akun staff aktif untuk mengelola alur booking, POS, dan monitoring unit real-time.',
}) {
    return (
        <div className="street-page">
            <div className="street-grid-overlay" />

            <div className="street-container flex min-h-screen items-center py-8 sm:py-10">
                <div className="grid w-full gap-6 lg:grid-cols-[1.08fr,0.92fr] lg:items-stretch">
                    <aside className="street-shell hidden rounded-[1.8rem] p-8 lg:flex lg:flex-col lg:justify-between">
                        <div>
                            <p className="street-heading-chip">{eyebrow}</p>
                            <h1 className="mt-5 text-4xl font-extrabold uppercase leading-[0.96] text-zinc-50">
                                {title}
                            </h1>
                            <p className="mt-4 max-w-lg text-base leading-7 text-zinc-300">{description}</p>
                        </div>

                        <div className="space-y-3">
                            {signals.map((item) => (
                                <div key={item} className="street-pill rounded-2xl px-4 py-3 text-sm font-semibold text-zinc-200">
                                    {item}
                                </div>
                            ))}
                        </div>
                    </aside>

                    <section className="street-shell rounded-[1.8rem] p-5 sm:p-7">
                        <div className="flex items-center justify-between gap-3">
                            <Link href={route('home')} className="street-motion hover:opacity-90">
                                <ApplicationLogo className="text-white" />
                            </Link>

                            <Link
                                href={route('home')}
                                className="street-pill street-motion rounded-full px-3 py-2 text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-200 hover:border-lime-200/40"
                            >
                                Back To Site
                            </Link>
                        </div>

                        <div className="mt-6 lg:hidden">
                            <p className="street-heading-chip">{eyebrow}</p>
                            <h1 className="mt-4 text-3xl font-extrabold uppercase leading-[0.96] text-zinc-50">
                                {title}
                            </h1>
                            <p className="mt-3 text-sm leading-6 text-zinc-300">{description}</p>
                        </div>

                        <div className="mt-7">{children}</div>
                    </section>
                </div>
            </div>
        </div>
    );
}
