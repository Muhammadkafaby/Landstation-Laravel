export default function ApplicationLogo({ className = '' }) {
    return (
        <div className={`inline-flex items-center gap-3 ${className}`}>
            <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-400 font-black text-zinc-950 shadow-lg shadow-emerald-500/20">
                LS
            </div>
            <div className="min-w-0">
                <div className="truncate text-sm font-semibold uppercase tracking-[0.24em] text-emerald-300">
                    Land Station
                </div>
                <div className="truncate text-xs text-zinc-400">
                    Gaming · Billiard · RC · Cafe
                </div>
            </div>
        </div>
    );
}
