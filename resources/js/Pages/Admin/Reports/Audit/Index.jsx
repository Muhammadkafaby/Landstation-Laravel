import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function AuditLogIndex({ logs, filters, actions, auditableTypes }) {
    const { data, setData, processing } = useForm({
        action: filters.action ?? '',
        actor: filters.actor ?? '',
        auditable_type: filters.auditable_type ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        router.get(route('reports.audit.index'), data, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    const clear = () => {
        setData({ action: '', actor: '', auditable_type: '' });

        router.get(route('reports.audit.index'), {
            action: '',
            actor: '',
            auditable_type: '',
        }, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Reports</p>
                        <h1 className="text-2xl font-bold text-white">Audit Logs</h1>
                    </div>
                    <Link
                        href={route('reports.index')}
                        className="inline-flex w-fit rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                    >
                        Kembali ke Reports
                    </Link>
                    <Link
                        href={route('reports.audit.export', {
                            action: filters.action,
                            actor: filters.actor,
                            auditable_type: filters.auditable_type,
                        })}
                        className="inline-flex w-fit rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300"
                    >
                        Export CSV
                    </Link>
                </div>
            }
        >
            <Head title="Audit Logs" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="rounded-3xl border border-white/10 bg-zinc-900/80 p-6 lg:p-8">
                        <div className="grid gap-3 md:grid-cols-[1fr,1fr,1fr,auto,auto]">
                            <select
                                className="rounded-full border border-white/10 bg-zinc-950/70 px-4 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none focus:ring-0"
                                value={data.action}
                                onChange={(event) => setData('action', event.target.value)}
                            >
                                <option value="">Semua action</option>
                                {actions.map((action) => (
                                    <option key={action} value={action}>{action}</option>
                                ))}
                            </select>

                            <input
                                className="rounded-full border border-white/10 bg-zinc-950/70 px-4 py-2 text-sm text-white placeholder:text-zinc-500 focus:border-emerald-500 focus:outline-none focus:ring-0"
                                placeholder="Actor user id"
                                value={data.actor}
                                onChange={(event) => setData('actor', event.target.value)}
                            />

                            <select
                                className="rounded-full border border-white/10 bg-zinc-950/70 px-4 py-2 text-sm text-white focus:border-emerald-500 focus:outline-none focus:ring-0"
                                value={data.auditable_type}
                                onChange={(event) => setData('auditable_type', event.target.value)}
                            >
                                <option value="">Semua auditable type</option>
                                {auditableTypes.map((auditableType) => (
                                    <option key={auditableType} value={auditableType}>{auditableType}</option>
                                ))}
                            </select>

                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex rounded-full bg-emerald-400 px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-300 disabled:opacity-50"
                            >
                                Filter
                            </button>

                            <button
                                type="button"
                                onClick={clear}
                                className="inline-flex rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                Clear
                            </button>
                        </div>
                    </form>

                    <div className="grid gap-6">
                        {logs.data.map((log) => (
                            <section key={log.id} className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">{log.action}</p>
                                        <h2 className="mt-2 text-xl font-semibold text-white">{log.actorName || 'System'}</h2>
                                        <p className="mt-2 text-sm text-zinc-400">{log.auditableType} · #{log.auditableId}</p>
                                    </div>
                                    <div className="rounded-full border border-white/10 bg-zinc-950/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-300">
                                        {log.createdAt}
                                    </div>
                                </div>

                                <pre className="mt-4 overflow-x-auto rounded-2xl border border-white/10 bg-zinc-950/70 p-4 text-xs text-zinc-300">
                                    {JSON.stringify(log.context, null, 2)}
                                </pre>
                            </section>
                        ))}
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {logs.links.map((link, index) => (
                            link.url ? (
                                <Link
                                    key={`${link.label}-${index}`}
                                    href={link.url}
                                    className={`rounded-full px-4 py-2 text-sm font-semibold transition ${link.active ? 'bg-emerald-400 text-zinc-950' : 'border border-white/15 text-white hover:bg-white/10'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={`${link.label}-${index}`}
                                    className="rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-zinc-500"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
