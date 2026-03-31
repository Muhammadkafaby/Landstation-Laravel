export default function ApplicationLogo({ className = '' }) {
    return (
        <div className={`inline-flex items-center gap-3 ${className}`}>
            <div className="relative flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl border border-lime-300/40 bg-zinc-950 text-xs font-extrabold tracking-[0.18em] text-lime-200">
                <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-lime-300 via-cyan-300 to-lime-300" />
                <span className="relative">LS</span>
            </div>

            <div className="min-w-0">
                <p className="truncate text-xs font-bold uppercase tracking-[0.24em] text-zinc-100">
                    Land Station
                </p>
                <p className="truncate text-[11px] font-medium tracking-[0.06em] text-zinc-400">
                    PS | RC | Billiard | Cafe
                </p>
            </div>
        </div>
    );
}
